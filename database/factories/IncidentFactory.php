<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Incident>
 */
class IncidentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => 1,
            'category' => fake()->randomElement(['accident', 'theft', 'vandalism', 'other']),
            'type' => fake()->randomElement(['minor', 'major', 'critical']),
            'date' => fake()->date(),
            'time' => fake()->time(),
            'location' => fake()->address(),
            'description' => fake()->sentence(),
            'media_url' => null,
            'severity_level' => fake()->randomElement(['low', 'medium', 'high']),
            'persons_of_interest' => fake()->randomElement(['John Doe', 'Jane Smith', 'Unknown']),
        ];
    }
}
