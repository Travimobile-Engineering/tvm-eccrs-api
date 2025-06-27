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

    protected $fillable = [
        'booking_id',
        'trip_id',
        'user_id',
        'payment_status',
    ];

    public function casts(){
        return [
            'confirmed' => 'boolean'
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

    #[Scope]
    protected function scopeCreatedBetween(Builder $query, $from, $to): void
    {
        $query->whereBetween('created_at', [$from, $to]);
    }
}
