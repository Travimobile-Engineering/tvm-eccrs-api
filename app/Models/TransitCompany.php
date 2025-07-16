<?php

namespace App\Models;

use App\Enums\Zones;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransitCompany extends Model
{
    use HasFactory;

    protected $connection = 'transport';

    protected $fillable = [
        'user_id',
        'name',
        'union_states_chapter',
        'type',
    ];

    public function union()
    {
        return $this->belongsTo(TransitCompanyUnion::class, 'union_id');
    }

    public function unionState()
    {
        return $this->belongsTo(State::class, 'union_states_chapter');
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'company_id');
    }

    public function drivers()
    {
        return $this->hasManyThrough(User::class, Vehicle::class, 'company_id', 'id', 'id', 'user_id');
    }

    public function bookings()
    {
        return $this->hasManyThrough(TripBooking::class, Trip::class, 'transit_company_id', 'trip_id');
    }

    public function activeTrips()
    {
        return $this->hasMany(Trip::class)->where('status', 'active');
    }

    #[Scope]
    public function scopeSignedUpBetween(Builder $query, $from, $to): void
    {
        $query->whereBetween('created_at', [$from, $to]);
    }

    #[Scope]
    public function scopeCountByType(Builder $query): array
    {
        return $query
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();
    }

    public static function booted()
    {
        static::addGlobalScope('zone', function (Builder $builder) {
            if (app('tempStore')->has('zoneId')) {
                $zone = Zone::find(app('tempStore')->get('zoneId'));
                if ($zone) {
                    $states = Zones::tryFrom($zone->name)?->states();
                    if ($states) {
                        $builder->whereIn('state', $states);
                    }
                }
            }
        });
    }
}
