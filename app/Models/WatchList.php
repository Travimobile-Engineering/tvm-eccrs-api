<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WatchList extends Model
{
    use HasFactory;

    protected $connection = 'transport';

    public function findUser()
    {
        return User::with(['tripBookings.trip' => fn($q) => $q->with('transitCompany', 'departureState', 'departureCity', 'destinationState', 'destinationCity')])
        ->where(function ($query) {
            $query->when($this->nin, function ($q) {
                $q->where('nin', $this->nin);
            });
            $query->when($this->phone, function ($q) {
                $q->orWhere('phone_number', $this->phone);
            });
            $query->when($this->email, function ($q) {
                $q->orWhere('email', $this->email);
            });
        })->first();
    }
}
