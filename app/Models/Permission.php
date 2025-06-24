<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $connection = 'authuser';

    protected $fillable = ['name'];

    protected $hidden = ['created_at', 'updated_at', 'pivot'];
}
