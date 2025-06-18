<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class RouteSubregion extends Model
{
    use HasFactory;

    protected $connection = 'transport';

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function departingTripBookings(){
        return $this->hasManyThrough(TripBooking::class, Trip::class, 'departure', 'trip_id', 'id', 'id');
    }

    public function arrivingTripBookings(){
        return $this->hasManyThrough(TripBooking::class, Trip::class, 'destination', 'trip_id', 'id', 'id');
    }
}
