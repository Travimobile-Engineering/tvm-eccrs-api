<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $connection = 'authuser';

    protected $fillable = ['name', 'description', 'login_enabled'];

    protected $hidden = ['created_at', 'updated_at', 'login_enabled'];

    protected function casts(): array
    {
        return [
            'login_enabled' => 'boolean',
        ];
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users()
    {
        return $this->belongsToMany(AuthUser::class, 'role_user', 'role_id', 'user_id');
    }
}
