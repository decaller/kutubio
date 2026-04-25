<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => str_pad((string) fake()->unique()->numberBetween(0, 999), 3, '0', STR_PAD_LEFT),
            'label' => fake()->words(3, true),
            'short_label' => fake()->optional()->word(),
            'color' => fake()->optional()->hexColor(),
            'sort_order' => fake()->numberBetween(1, 1000),
            'source_version' => 'factory',
        ];
    }
}
