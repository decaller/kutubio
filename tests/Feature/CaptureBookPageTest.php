<?php

namespace Tests\Feature;

use App\Enums\CaptureSessionStatus;
use App\Enums\MetadataRevisionType;
use App\Filament\Pages\CaptureBook;
use App\Models\CaptureSession;
use App\Models\MetadataRevision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CaptureBookPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_render_capture_page(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(CaptureBook::getUrl())->assertOk();
    }

    public function test_submit_requires_front_and_back_images(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CaptureBook::class)
            ->set('frontImageData', $this->imageDataUrl())
            ->call('submit')
            ->assertHasErrors(['backImageData' => 'required']);
    }

    public function test_submit_creates_capture_session_and_raw_revision(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(CaptureBook::class)
            ->set('frontImageData', $this->imageDataUrl())
            ->set('backImageData', $this->imageDataUrl())
            ->set('frontImageWidth', 1)
            ->set('frontImageHeight', 1)
            ->set('backImageWidth', 1)
            ->set('backImageHeight', 1)
            ->call('submit')
            ->assertRedirect();

        $captureSession = CaptureSession::firstOrFail();

        $this->assertSame($user->id, $captureSession->submitted_by);
        $this->assertSame(CaptureSessionStatus::Captured, $captureSession->status);
        $this->assertNotNull($captureSession->submitted_at);
        $this->assertSame(['mime_type' => 'image/png', 'size_bytes' => 68, 'width' => 1, 'height' => 1], $captureSession->front_image_meta);
        $this->assertSame(['mime_type' => 'image/png', 'size_bytes' => 68, 'width' => 1, 'height' => 1], $captureSession->back_image_meta);

        Storage::disk('public')->assertExists($captureSession->front_image_path);
        Storage::disk('public')->assertExists($captureSession->back_image_path);

        $revision = MetadataRevision::firstOrFail();

        $this->assertSame($captureSession->id, $revision->capture_session_id);
        $this->assertSame(MetadataRevisionType::RawCapture, $revision->revision_type);
        $this->assertSame('capture_page', $revision->source_stage);
        $this->assertSame($captureSession->front_image_path, $revision->payload['front_image_path']);
        $this->assertSame($captureSession->back_image_path, $revision->payload['back_image_path']);
    }

    private function imageDataUrl(): string
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=';
    }
}
