<?php

namespace App\Services\Actions;

use App\Models\RouteSubregion;
use App\Models\State;
use App\Models\TripBooking;

class UserActionService
{
    public function getZoneActivities(array $zone): int
    {
        $cities = State::getZoneCities($zone);

        return TripBooking::whereHas('trip', function ($query) use ($cities) {
            $query->whereIn('departure', $cities)
                ->orWhereIn('destination', $cities);
        })->count();
    }

    public function getStateActivityCount(string $state, $showCities = false): mixed
    {
        $state = State::with('cities')->where('name', $state)->first();

        if ($showCities) {

            $city_ids = $state->cities->map(fn ($city) => $city->id);
            $cities = RouteSubregion::with('departingTripBookings', 'arrivingTripBookings')->whereIn('id', $city_ids)->get();
            $data = collect();
            $cities->each(function ($city) use ($data) {
                $data[$city->name] = $city->departingTripBookings->count() + $city->arrivingTripBookings->count();
            });

            return $data;
        } else {
            $cities = $state->cities->map(fn ($city) => $city->id);

            return TripBooking::whereHas('trip', function ($query) use ($cities) {
                $query->whereIn('departure', $cities)
                    ->orWhereIn('destination', $cities);
            })->count();
        }
    }
}
