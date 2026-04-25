<?php

namespace App\Jobs;

use App\Enums\CaptureSessionStatus;
use App\Enums\MetadataRevisionType;
use App\Models\CaptureSession;
use App\Models\MetadataRevision;
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
     * Create a new job instance.
     */
    public function __construct(
        public CaptureSession $captureSession
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! $this->captureSession->front_image_path) {
            Log::warning("Capture session {$this->captureSession->public_id} has no front image for vision extraction.");
            return;
        }

        // TODO: Integrate with actual Vision API (e.g., Anthropic Claude Vision or OpenAI GPT-4V)
        // For now, we simulate a successful extraction.
        
        $simulatedData = [
            'title' => 'Sample Book Title (OCR Mock)',
            'confidence_score' => 0.85,
            'model' => 'mock-vision-v1',
            'extracted_at' => now()->toIso8601String(),
        ];

        MetadataRevision::create([
            'capture_session_id' => $this->captureSession->id,
            'revision_type' => MetadataRevisionType::LlmDraft,
            'source_stage' => 'vision_extraction',
            'source_actor_type' => self::class,
            'source_actor_id' => 0, // System
            'payload' => array_merge($simulatedData, [
                'front_image_path' => $this->captureSession->front_image_path,
                'notes' => 'Automatic vision extraction completed.',
            ]),
            'source_meta' => [
                'model_version' => 'mock-vision-v1',
                'processing_duration_ms' => 1200,
            ],
        ]);

        $this->captureSession->update([
            'status' => CaptureSessionStatus::Processing, // Or keep as captured if awaiting more steps
        ]);
    }
}
