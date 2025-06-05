<?php

namespace App\Models;

use App\Enums\Zones;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $connection = "transport";

    public static function getZoneStates(array $zone){
        return self::whereIn('name', $zone)->get(['name', 'id']);
    }

    public static function getZoneCities(array $zone){
        return self::with('cities:id,name,state_id')
            ->whereIn('name', $zone)
            ->get(['name', 'id'])
            ->map(function($state){
                return $state->cities->map(fn($city) => $city->id);
            })->flatten();
    }

    public function cities(){
        return $this->hasMany(RouteSubregion::class);
    }

}

