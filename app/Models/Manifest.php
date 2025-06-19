<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manifest extends Model
{
    protected $connection = 'transport';
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}
