<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouteSubregion extends Model
{
    use HasFactory;

    protected $connection = 'transport';

    public function state()
    {
        return $this->belongsTo(State::class);
    }
}
