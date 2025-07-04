<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WatchList extends Model
{
    use HasFactory;

    protected $connection = 'transport';

    public function userByNin(){
        return $this->belongsTo(User::class, 'nin', 'nin');
    }

    public function userByPhone(){
        return $this->belongsTo(User::class, 'phone', 'phone_number');
    }

    public function userByEmail(){
        return $this->belongsTo(User::class, 'email', 'email');
    }
}
