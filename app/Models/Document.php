<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'number',
        'expiration_date',
        'status',
    ];
}
