<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'subtitle' => fake()->optional()->sentence(4),
            'authors_display' => fake()->name(),
            'isbn13' => fake()->optional()->isbn13(),
            'publisher' => fake()->optional()->company(),
            'page_count' => fake()->optional()->numberBetween(24, 900),
            'synopsis' => fake()->optional()->paragraph(),
            'category_id' => Category::factory(),
        ];
    }
}
