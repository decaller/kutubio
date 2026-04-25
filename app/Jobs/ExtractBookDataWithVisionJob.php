<?php

namespace App\Jobs;

use App\Enums\CaptureSessionStatus;
use App\Enums\MetadataRevisionType;
use App\Models\CaptureSession;
use App\Models\MetadataRevision;
use App\Services\OllamaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExtractBookDataWithVisionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public CaptureSession $captureSession
    ) {}

    /**
     * Execute the job.
     */
    public function handle(OllamaService $ollama): void
    {
        if (! $this->captureSession->front_image_path) {
            Log::warning("Capture session {$this->captureSession->public_id} has no front image for vision extraction.");
            return;
        }

        try {
            $prompt = "Extract the following metadata from this book cover image. Respond ONLY with a JSON object containing: 'title' (full book title), 'authors' (array of strings), 'publisher' (string, if visible), and 'subtitle' (string, if visible). If a field is not found, use null.";

            $response = $ollama->extractFromImage(
                $this->captureSession->front_image_path,
                $prompt
            );

            $data = json_decode($response['response'] ?? '{}', true);

            if (empty($data['title'])) {
                throw new \Exception("Could not extract title from image.");
            }

            MetadataRevision::create([
                'capture_session_id' => $this->captureSession->id,
                'revision_type' => MetadataRevisionType::LlmDraft,
                'source_stage' => 'vision_extraction',
                'source_actor_type' => self::class,
                'source_actor_id' => 0, // System
                'payload' => array_merge($data, [
                    'front_image_path' => $this->captureSession->front_image_path,
                    'notes' => 'Automatic vision extraction using ' . config('services.ollama.vision_model', 'qwen3-vl:8b'),
                ]),
                'source_meta' => [
                    'model' => config('services.ollama.vision_model', 'qwen3-vl:8b'),
                    'raw_response' => $response['response'] ?? null,
                ],
            ]);

            $this->captureSession->update([
                'status' => CaptureSessionStatus::Processing,
            ]);

        } catch (\Exception $e) {
            Log::error("Vision extraction failed for session {$this->captureSession->public_id}: " . $e->getMessage());
            
            $this->captureSession->update([
                'status' => CaptureSessionStatus::Failed,
                'failure_reason' => "AI Vision extraction failed: " . $e->getMessage(),
            ]);
            
            throw $e;
        }
    }
}
