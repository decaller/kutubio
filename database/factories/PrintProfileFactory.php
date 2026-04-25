<?php

namespace Database\Factories;

use App\Models\PrintProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrintProfile>
 */
class PrintProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'page_width_mm' => 210,
            'page_height_mm' => 297,
            'grid_columns' => 3,
            'grid_rows' => 8,
            'offset_x_mm' => fake()->randomFloat(2, 0, 5),
            'offset_y_mm' => fake()->randomFloat(2, 0, 5),
            'slot_width_mm' => 63.5,
            'slot_height_mm' => 33.9,
            'is_default' => false,
        ];
    }
}
