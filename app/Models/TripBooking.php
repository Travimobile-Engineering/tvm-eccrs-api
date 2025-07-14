<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripBooking extends Model
{
    use HasFactory;

    protected $connection = 'transport';
    protected static $zoneId = null;

    protected $fillable = [
        'booking_id',
        'trip_id',
        'user_id',
        'payment_status',
    ];

    public function casts()
    {
        return [
            'confirmed' => 'boolean',
            'on_seat' => 'boolean',
            'travelling_with' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function travellingWith()
    {
        return $this->hasMany(TripBookingPassenger::class);
    }

    public function tripBookingPassengers()
    {
        return $this->hasMany(TripBookingPassenger::class);
    }

    #[Scope]
    protected function scopeCreatedBetween(Builder $query, $from, $to): void
    {
        $query->whereBetween('created_at', [$from, $to]);
    }

    public function setZoneId($zoneId)
    {
        self::$zoneId = $zoneId;
    }

    public static function booted()
    {
        static::addGlobalScope('zone', function(Builder $builder){
            if(!empty(self::$zoneId)){
                $builder->whereHas('trip', function($q){
                    $q->where('zone_id', self::$zoneId);
                });
            }
        });
    }
}
