<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'address',
    ];

    public function users()
    {
        return $this->hasMany(AuthUser::class, 'organization_id');
    }
}
