<?php

namespace Database\Factories;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransitCompany>
 */
class TransitCompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "user_id" => \App\Models\User::inRandomOrder()->limit(1)->first()->id,
            "name" => fake()->company,
            "union_states_chapter" => DB::connection('transport')->table('states')->inRandomOrder()->limit(1)->first()->id,
            "type" => fake()->randomElement(['road', 'sea', 'air', 'rail'])
        ];
    }
}
