<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RouteSubregion>
 */
class RouteSubregionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $state = DB::connection('transport')->table('states')->inRandomOrder()->limit(1)->first();
        $region = DB::connection('transport')->table('route_regions')->where(['name' => $state->name])->first();

        return [
            'state_id' => $state->id,
            'region_id' => $region->id ?? DB::connection('transport')->table('route_regions')->insertGetId(['name' => $state->name]),
            'name' => fake()->streetName(),
        ];
    }
}
