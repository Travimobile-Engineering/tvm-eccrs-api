<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $connection = 'transport';

    public static function getZoneStates(array $zone)
    {
        return self::whereIn('name', $zone)->get(['name', 'id']);
    }

    public static function getZoneCities(array $zone)
    {
        return self::with('cities:id,name,state_id')
            ->whereIn('name', $zone)
            ->get(['name', 'id'])
            ->map(function ($state) {
                return $state->cities->map(fn ($city) => $city->id);
            })->flatten();
    }

    public function cities()
    {
        return $this->hasMany(RouteSubregion::class);
    }

    public function departingTrips()
    {
        return $this->hasManyThrough(Trip::class, RouteSubregion::class, 'id', 'departure', 'id');
    }

    public function arrivingTrips()
    {
        return $this->hasManyThrough(Trip::class, RouteSubregion::class, 'id', 'destination', 'id');
    }

    public function transitCompanies()
    {
        return $this->hasMany(TransitCompany::class, 'state', 'name');
    }
}
