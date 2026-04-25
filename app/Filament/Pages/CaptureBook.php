<?php

namespace App\Filament\Pages;

use App\Enums\CaptureSessionStatus;
use App\Enums\MetadataRevisionType;
use App\Filament\Resources\CaptureSessions\CaptureSessionResource;
use App\Models\CaptureSession;
use App\Models\MetadataRevision;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use UnitEnum;

class CaptureBook extends Page
{
    protected string $view = 'filament.pages.capture-book';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCamera;

    protected static string|UnitEnum|null $navigationGroup = 'Intake';

    protected static ?int $navigationSort = 5;

    public static ?string $title = 'Capture Book';
    
    public ?string $frontImageData = null;

    public ?string $backImageData = null;

    public ?int $frontImageWidth = null;


    public ?int $frontImageHeight = null;

    public ?int $backImageWidth = null;

    public ?int $backImageHeight = null;

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'frontImageData' => ['required', 'string'],
            'backImageData' => ['required', 'string'],
            'frontImageWidth' => ['nullable', 'integer', 'min:1'],
            'frontImageHeight' => ['nullable', 'integer', 'min:1'],
            'backImageWidth' => ['nullable', 'integer', 'min:1'],
            'backImageHeight' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function submit(): void
    {
        $this->validate();

        $captureSession = DB::transaction(function (): CaptureSession {
            $captureSession = CaptureSession::create([
                'submitted_by' => auth()->id(),
                'status' => CaptureSessionStatus::Captured,
                'submitted_at' => now(),
            ]);

            $frontImage = $this->storeCapturedImage($this->frontImageData, $captureSession->public_id, 'front');
            $backImage = $this->storeCapturedImage($this->backImageData, $captureSession->public_id, 'back');

            $captureSession->update([
                'front_image_path' => $frontImage['path'],
                'back_image_path' => $backImage['path'],
                'front_image_meta' => [
                    'mime_type' => $frontImage['mime_type'],
                    'size_bytes' => $frontImage['size_bytes'],
                    'width' => $this->frontImageWidth,
                    'height' => $this->frontImageHeight,
                ],
                'back_image_meta' => [
                    'mime_type' => $backImage['mime_type'],
                    'size_bytes' => $backImage['size_bytes'],
                    'width' => $this->backImageWidth,
                    'height' => $this->backImageHeight,
                ],
            ]);

            MetadataRevision::create([
                'capture_session_id' => $captureSession->id,
                'revision_type' => MetadataRevisionType::RawCapture,
                'source_stage' => 'capture_page',
                'source_actor_type' => auth()->user()::class,
                'source_actor_id' => auth()->id(),
                'payload' => [
                    'front_image_path' => $frontImage['path'],
                    'back_image_path' => $backImage['path'],
                    'notes' => 'Raw browser camera capture submitted for review.',
                ],
                'source_meta' => [
                    'capture_page_version' => 'browser_auto_capture_v1',
                    'front_image_meta' => $captureSession->front_image_meta,
                    'back_image_meta' => $captureSession->back_image_meta,
                ],
            ]);

            return $captureSession;
        });

        Notification::make()
            ->title('Capture session saved')
            ->body("Session {$captureSession->public_id} is ready for review.")
            ->success()
            ->send();

        $this->redirect(CaptureSessionResource::getUrl('view', ['record' => $captureSession]) . '?autoback=1');
    }

    /**
     * @return array{path: string, mime_type: string, size_bytes: int}
     */
    private function storeCapturedImage(?string $dataUrl, string $publicId, string $side): array
    {
        if (! is_string($dataUrl) || ! preg_match('/^data:(image\/(?:jpeg|png|webp));base64,(.+)$/', $dataUrl, $matches)) {
            throw ValidationException::withMessages([
                "{$side}ImageData" => 'The captured image payload is invalid.',
            ]);
        }

        $bytes = base64_decode($matches[2], strict: true);

        if ($bytes === false) {
            throw ValidationException::withMessages([
                "{$side}ImageData" => 'The captured image could not be decoded.',
            ]);
        }

        $extension = match ($matches[1]) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };

        $path = "capture-sessions/{$publicId}/{$side}.{$extension}";

        Storage::disk('public')->put($path, $bytes, 'public');

        return [
            'path' => $path,
            'mime_type' => $matches[1],
            'size_bytes' => strlen($bytes),
        ];
    }
}
