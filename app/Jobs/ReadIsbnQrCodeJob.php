<?php

namespace App\Jobs;

use App\Enums\CaptureSessionStatus;
use App\Enums\MetadataRevisionType;
use App\Enums\QrParseStatus;
use App\Models\CaptureSession;
use App\Models\MetadataRevision;
use App\Services\OllamaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReadIsbnQrCodeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        Log::info("Starting ReadIsbnQrCodeJob for session {$this->captureSession->public_id}");

        if ($this->captureSession->status !== CaptureSessionStatus::Processing) {
            $this->captureSession->update([
                'status' => CaptureSessionStatus::Processing,
                'processing_started_at' => now(),
            ]);
        }

        $this->captureSession->update(['current_processing_stage' => 'Reading ISBN QR...']);

        if (! $this->captureSession->back_image_path) {
            Log::warning("Capture session {$this->captureSession->public_id} has no back image for QR reading.");

            return;
        }

        try {
            $prompt = "Identify and decode any QR code visible on this book's back cover. Respond ONLY with a JSON object containing: 'qr_found' (boolean), 'decoded_text' (string or null), and 'type' (string, e.g., 'copy_id', 'isbn', 'unknown'). If no QR code is found, set qr_found to false.";

            $response = $ollama->extractFromImage(
                $this->captureSession->back_image_path,
                $prompt
            );

            $data = json_decode($response['response'] ?? '{}', true);

            if ($data['qr_found'] ?? false) {
                MetadataRevision::create([
                    'capture_session_id' => $this->captureSession->id,
                    'revision_type' => MetadataRevisionType::QrParse,
                    'source_stage' => 'qr_reading',
                    'source_actor_type' => self::class,
                    'source_actor_id' => 0,
                    'payload' => array_merge($data, [
                        'back_image_path' => $this->captureSession->back_image_path,
                        'notes' => 'QR code detected and decoded by AI.',
                    ]),
                    'source_meta' => [
                        'model' => config('services.ollama.vision_model'),
                    ],
                ]);

                $this->captureSession->update([
                    'decoded_qr_payload' => $data['decoded_text'],
                    'qr_parse_status' => QrParseStatus::ValidKnownCopy, // Simplified for now
                ]);
            } else {
                $this->captureSession->update([
                    'qr_parse_status' => QrParseStatus::Missing,
                ]);
            }

        } catch (\Exception $e) {
            Log::error("QR reading failed for session {$this->captureSession->public_id}: ".$e->getMessage());

            $this->captureSession->update([
                'qr_parse_status' => QrParseStatus::Unreadable,
                'failure_reason' => 'QR reading failed: '.$e->getMessage(),
            ]);

            throw $e;
        }
    }
}
