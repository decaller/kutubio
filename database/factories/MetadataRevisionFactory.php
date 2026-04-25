<?php

namespace Database\Factories;

use App\Enums\MetadataRevisionType;
use App\Models\Book;
use App\Models\CaptureSession;
use App\Models\MetadataRevision;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MetadataRevision>
 */
class MetadataRevisionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'book_id' => Book::factory(),
            'capture_session_id' => CaptureSession::factory(),
            'revision_type' => fake()->randomElement(MetadataRevisionType::cases()),
            'source_stage' => 'factory',
            'source_actor_type' => 'system',
            'source_actor_id' => null,
            'confidence_score' => fake()->randomFloat(4, 0, 1),
            'payload' => [
                'title' => fake()->sentence(3),
                'authors' => [fake()->name()],
                'notes' => fake()->sentence(),
            ],
            'diff_from_previous' => null,
            'source_meta' => ['factory' => true],
        ];
    }
}
