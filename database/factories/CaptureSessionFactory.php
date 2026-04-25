<?php

namespace Database\Factories;

use App\Enums\CaptureSessionStatus;
use App\Enums\QrParseStatus;
use App\Models\CaptureSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CaptureSession>
 */
class CaptureSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'submitted_by' => User::factory(),
            'status' => fake()->randomElement(CaptureSessionStatus::cases()),
            'front_image_path' => fake()->optional()->filePath(),
            'back_image_path' => fake()->optional()->filePath(),
            'front_image_meta' => ['width' => 1200, 'height' => 1600],
            'back_image_meta' => ['width' => 1200, 'height' => 1600],
            'decoded_qr_payload' => fake()->optional()->bothify('kutubio:copy:v1:01????????????????????????'),
            'qr_parse_status' => fake()->optional()->randomElement(QrParseStatus::cases()),
            'failure_reason' => null,
            'submitted_at' => now(),
        ];
    }
}
