<?php

namespace Database\Factories;

use App\Models\Book;
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
            'title' => fake()->catchPhrase(),
            'author' => fake()->name(),
            'isbn' => fake()->unique()->isbn13(),
            'category' => fake()->randomElement(['Computer Science', 'Literature', 'History', 'Philosophy', 'Science']),
            'published_year' => fake()->numberBetween(1990, 2026),
            'stock' => fake()->numberBetween(1, 50),
            'description' => fake()->paragraph(),
        ];
    }
}
