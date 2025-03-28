<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Journal>
 */
class JournalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            [
                "title" => fake()->text(),
                "body" => fake()->text(),
                "stress_level" => fake()->randomDigit(5),
                "mood_level" => "good"
            ]
        ];
    }
}
