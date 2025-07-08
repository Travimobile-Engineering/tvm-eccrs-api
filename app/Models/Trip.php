<?php

namespace App\Models;

use App\Actions\SystemLogAction;
use App\Dtos\SystemLogData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $connection = 'transport';

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

    protected static function booted()
    {
        static::created(function ($model) {
            $dto = new SystemLogData(
                'Created new resource',
                $model,
                $model->id,
                'created',
                request()->ip(),
                null,
                $model->getAttributes(),
                request()->fullUrl()
            );

            app(SystemLogAction::class)->execute($dto);
        });

        static::updated(function ($model) {
            $dto = new SystemLogData(
                'Updated resource',
                $model,
                $model->id,
                'updated',
                request()->ip(),
                null,
                $model->getAttributes(),
                request()->fullUrl()
            );

            app(SystemLogAction::class)->execute($dto);
        });

        static::deleted(function ($model) {
            $dto = new SystemLogData(
                'Deleted resource',
                $model,
                $model->id,
                'deleted',
                request()->ip(),
                null,
                $model->getAttributes(),
                request()->fullUrl()
            );

            app(SystemLogAction::class)->execute($dto);
        });
    }

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
}
