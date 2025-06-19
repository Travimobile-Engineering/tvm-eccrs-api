<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->lastName,
            'company_id' => \App\Models\TransitCompany::factory(),
            'user_id' => \App\Models\User::inRandomOrder()->limit(1)->pluck('id')->first(),
            'brand_id' => fake()->randomNumber(),
            'plate_no' => strtoupper(Str::random(2)).fake()->randomNumber(3).strtoupper(Str::random(2)),
            'engine_no' => Str::random(),
            'chassis_no' => Str::random(),
            'color' => fake()->colorName,
            'model' => fake()->year,
            'seats' => ['C1', 'D4', 'B3'],
        ];
    }
}
