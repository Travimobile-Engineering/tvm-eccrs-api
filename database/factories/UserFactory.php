<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "uuid" => Str::random(),
            "first_name" => fake()->firstName,
            "last_name" => fake()->lastName,
            'password' => static::$password ??= Hash::make('password'),
            "phone_number" => fake()->phoneNumber,
            'email' => fake()->unique()->safeEmail(),
            "nin" => fake()->randomNumber(9),
            "next_of_kin_full_name" => fake()->name,
            "email_verified" => 1,
            "verification_code" => fake()->randomNumber(4),
            "status" => 'active',
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
