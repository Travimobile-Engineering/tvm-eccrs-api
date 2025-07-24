<?php

namespace App\Models;

use App\Actions\SystemLogAction;
use App\Dtos\SystemLogData;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripBooking extends Model
{
    use HasFactory;

    protected $connection = 'transport';

    protected $fillable = [
        'booking_id',
        'trip_id',
        'user_id',
        'payment_status',
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

        if (app('tempStore')->has('zoneId')) {
            static::addGlobalScope('zone', function (Builder $builder) {
                $builder->whereHas('trip');
            });
        }
    }

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

    public function confirmedPassengers(){
        return $this->hasMany(TripBookingPassenger::class)->where('on_seat', 1);
    }

    #[Scope]
    protected function scopeCreatedBetween(Builder $query, $from, $to): void
    {
        $query->whereBetween('created_at', [$from, $to]);
    }
}
