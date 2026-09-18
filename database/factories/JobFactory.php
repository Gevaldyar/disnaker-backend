<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Job>
 */
class JobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => null,
            'title' => fake()->jobTitle(),
            'poster' => null,
            'location' => fake()->city(),
            'description' => fake()->paragraphs(3, true),
            'published_at' => now(),
            'expires_at' => today()->addDays(30),
            'views' => 0,
            'status' => 'approved',
        ];
    }
}