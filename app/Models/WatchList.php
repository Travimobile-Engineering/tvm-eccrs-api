<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WatchList extends Model
{
    use HasFactory;

    protected $connection = 'transport';

    public function scopeWhereStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function state(){
        return $this->belongsTo(State::class);
    }
}
