<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manifest extends Model
{
    public function trip(){
        return $this->hasOne(Trip::class, 'id', 'trip_id');
    }
}
