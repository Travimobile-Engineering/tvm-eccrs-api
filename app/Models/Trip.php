<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $connection = 'transport';

    protected $fillable = [
        "user_id",
        "uuid",
        "vehicle_id",
        "transit_company_id",
        "departure",
        "destination",
        "price",
        "bus_type",
        "bus_stops",
    ];

    public function casts(): array{
        return [
            "bus_stops" => 'array',
        ];
    }

    public function transitCompany(){
        return $this->belongsTo(TransitCompany::class, 'transit_company_id', 'id');
    }

    public function departureCity(){
        return $this->hasOne(RouteSubregion::class, 'id', 'departure');
    }

    public function destinationCity(){
        return $this->hasOne(RouteSubregion::class, 'id', 'destination');
    }

    public function manifest(){
        return $this->hasOne(Manifest::class);
    }

    public function booking(){
        return $this->hasMany(TripBooking::class);
    }
}
