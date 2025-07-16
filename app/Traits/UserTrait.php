<?php

namespace App\Traits;

use App\Models\Role;
use Illuminate\Support\Facades\Schema;

trait UserTrait
{
    public static function createRole(string $name, string $description, array $permissions)
    {
        $role = Role::create([
            'name' => $name,
            'description' => $description,
        ]);

        $role->permissions()->sync($permissions);

        return $role;
    }

    public static function getRoles()
    {
        return Role::with('permissions:id,name')->get()->map(function ($role) {
            $role->has_users = $role->users()->exists();

            return $role;
        });
    }

    public static function getRole(int $id)
    {
        $role = Role::with('permissions:id,name')->find($id);

        if ($role) {
            $role->has_users = $role->users()->exists();
        }

        return $role;
    }

    public static function updateRole(int $id, string $name, string $description, array $permissions)
    {
        $role = self::getRole($id);

        if (! $role) {
            return null;
        }

        $role->update([
            'name' => $name,
            'description' => $description,
        ]);

        $role->permissions()->sync($permissions);

        return $role;
    }

    public static function deleteRole(int $id)
    {
        $role = self::getRole($id);

        if (! $role) {
            return null;
        }

        $role->delete();

        return true;
    }

    protected function setZoneId()
    {
        if (! empty(request('zone_id'))) {
            app('tempStore')->store('zoneId', request('zone_id'));
        }
    }

    protected function sortColumn($sort, $table = 'users')
    {
        $column = explode(',', $sort)[0] ?? 'created_at';
        if (Schema::hasColumn($table, $column)) {
            return $column;
        }
    }

    protected function sortDirection($sort)
    {
        $direction = explode(',', $sort)[1] ?? 'desc';

        return in_array($direction, ['asc', 'desc']) ? $direction : 'desc';
    }
}
