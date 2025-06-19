<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trip>
 */
class TripFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $vehicle = \App\Models\Vehicle::factory()->create();

        return [
            'user_id' => \App\Models\User::factory(),
            'uuid' => Str::random(),
            'vehicle_id' => $vehicle->id,
            'transit_company_id' => $vehicle->company_id,
            'departure' => \App\Models\RouteSubregion::factory(),
            'destination' => \App\Models\RouteSubregion::factory(),
            'price' => fake()->randomNumber(5),
            'bus_type' => 'car',
            'bus_stops' => [],
        ];
    }
}
