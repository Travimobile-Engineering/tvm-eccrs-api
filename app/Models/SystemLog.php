<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'user_id',
        'activity',
        'model',
        'model_id',
        'ip_address',
        'user_agent',
        'event_type',
        'url',
        'old_values',
        'new_values',
    ];

    public function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(AuthUser::class, 'user_id');
    }
}
