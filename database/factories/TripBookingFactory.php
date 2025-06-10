<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TripBooking>
 */
class TripBookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Str::random(),
            'user_id' => \App\Models\User::factory(),
            'trip_id' => \App\Models\Trip::factory(),
            'payment_status' => 0,
        ];
    }
}
