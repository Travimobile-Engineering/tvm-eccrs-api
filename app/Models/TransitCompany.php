<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransitCompany extends Model
{
    use HasFactory;

    protected $connection = 'transport';

    protected $fillable = [
        "user_id",
        "name",
        "union_states_chapter",
        "type",
    ];

    public function union(){
        return $this->belongsTo(TransitCompanyUnion::class, 'union_id');
    }

    public function unionState(){
        return $this->belongsTo(State::class, 'union_states_chapter');
    }

    public function vehicles(){
        return $this->hasMany(Vehicle::class, 'company_id');
    }
    
    public function drivers(){
        return $this->hasMany(Vehicle::class, 'company_id')->whereNotNull('user_id');
    }

    public function bookings(){
        return $this->hasManyThrough(TripBooking::class, Trip::class, 'transit_company_id', 'trip_id');
    }
}
