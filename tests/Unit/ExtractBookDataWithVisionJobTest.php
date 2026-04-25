<?php

namespace Tests\Unit;

use App\Enums\CaptureSessionStatus;
use App\Jobs\ExtractBookDataWithVisionJob;
use App\Models\CaptureSession;
use App\Services\OllamaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class ExtractBookDataWithVisionJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_uses_qwen_thinking_json_when_response_is_empty(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('captures/front.jpg', 'fake-image-bytes');

        $captureSession = CaptureSession::factory()->create([
            'status' => CaptureSessionStatus::Captured,
            'front_image_path' => 'captures/front.jpg',
        ]);

        $ollama = Mockery::mock(OllamaService::class);
        $ollama->shouldReceive('extractFromImage')
            ->once()
            ->with('captures/front.jpg', Mockery::type('string'))
            ->andReturn([
                'response' => '',
                'thinking' => json_encode([
                    'title' => 'MENUMBUHKAN KESADARAN BERAMAL',
                    'subtitle' => 'Telaah dan Panduan Penumbuhan Kesadaran Beramal Merujuk Pola Pendidikan Nabawiyyah',
                    'authors' => 'Abdul Kholiq & Bayu Issetyadi',
                    'publisher' => 'HUD',
                    'confidence' => 0.95,
                ]),
            ]);

        (new ExtractBookDataWithVisionJob($captureSession))->handle($ollama);

        $revision = $captureSession->metadataRevisions()->where('source_stage', 'vision_extraction')->firstOrFail();

        $this->assertSame('MENUMBUHKAN KESADARAN BERAMAL', $revision->payload['title']);
        $this->assertSame(['Abdul Kholiq', 'Bayu Issetyadi'], $revision->payload['authors']);
        $this->assertSame('qwen3-vl:8b', $revision->source_meta['model']);
        $this->assertSame(CaptureSessionStatus::NeedsReview, $captureSession->fresh()->status);
    }
}
