<?php

namespace App\Models;

use App\Enums\Zones;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Trip extends Model
{
    use HasFactory;

    protected $connection = 'transport';
    protected static $zoneId = null;

    protected $fillable = [
        'user_id',
        'uuid',
        'vehicle_id',
        'transit_company_id',
        'departure',
        'destination',
        'price',
        'bus_type',
        'bus_stops',
    ];

    public function casts(): array
    {
        return [
            'bus_stops' => 'array',
        ];
    }

    public function transitCompany()
    {
        return $this->belongsTo(TransitCompany::class, 'transit_company_id', 'id');
    }

    public function departureCity()
    {
        return $this->hasOne(RouteSubregion::class, 'id', 'departure');
    }

    public function destinationCity()
    {
        return $this->hasOne(RouteSubregion::class, 'id', 'destination');
    }

    public function manifest()
    {
        return $this->hasOne(Manifest::class);
    }

    public function bookings()
    {
        return $this->hasMany(TripBooking::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function departureState()
    {
        return $this->hasOneThrough(State::class, RouteSubregion::class, 'id', 'id', 'departure', 'state_id');
    }

    public function destinationState()
    {
        return $this->hasOneThrough(State::class, RouteSubregion::class, 'id', 'id', 'destination', 'state_id');
    }

    public function scopeBetween($query, $from, $to)
    {
        $query->whereBetween('created_at', [$from, $to]);
    }

    public function setZoneId($zoneId)
    {
        self::$zoneId = $zoneId;
    }

    public static function booted()
    {
        static::addGlobalScope('zone', function ($builder) {
            if (!empty(self::$zoneId)) {
                
                $zone = Zone::find(self::$zoneId)->name;
                $states = Zones::tryFrom($zone)?->states();
                
                $builder->where(function ($query) use ($states) {
                    $query->whereHas('departureState', function ($query) use ($states) {
                        return $query->whereIn('states.name', $states);
                    })
                    ->orWhereHas('destinationState', function ($query) use ($states) {
                        return $query->whereIn('states.name', $states);
                    });
                });
            }
        });
    }
}
