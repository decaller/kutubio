<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Exception;

class OllamaService
{
    protected string $baseUrl;
    protected string $model;

    public function __construct()
    {
        $this->baseUrl = config('services.ollama.url');
        $this->model = config('services.ollama.vision_model');
    }

    /**
     * @param string $imagePath Relative path in 'public' disk
     * @param string $prompt
     * @return array
     * @throws Exception
     */
    public function extractFromImage(string $imagePath, string $prompt): array
    {
        if (!Storage::disk('public')->exists($imagePath)) {
            throw new Exception("Image not found: {$imagePath}");
        }

        $imageData = base64_encode(Storage::disk('public')->get($imagePath));

        $response = Http::timeout(60)->post("{$this->baseUrl}/api/generate", [
            'model' => $this->model,
            'prompt' => $prompt,
            'images' => [$imageData],
            'stream' => false,
            'format' => 'json',
        ]);

        if ($response->failed()) {
            throw new Exception("Ollama API request failed: " . $response->body());
        }

        return $response->json();
    }
}
