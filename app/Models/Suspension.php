<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suspension extends Model
{
    protected $connection = 'authuser';

    protected $table = 'suspensions';

    protected $fillable = [
        'user_id',
        'suspended_by',
        'reason',
        'explanation',
        'indefinite',
        'end_date',
        'lifted_at',
    ];

    protected function casts(): array
    {
        return [
            'indefinite' => 'boolean',
            'end_date' => 'date',
            'lifted_at' => 'datetime',
        ];
    }

    public function suspensions()
    {
        return $this->hasMany(Suspension::class, 'user_id');
    }

    public function latestSuspension()
    {
        return $this->hasOne(Suspension::class, 'user_id')->latestOfMany();
    }
}
