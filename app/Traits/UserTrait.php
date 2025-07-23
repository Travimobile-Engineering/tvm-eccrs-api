<?php

namespace App\Traits;

use App\Models\Role;

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
            if (is_numeric(request('zone_id'))) {
                app('tempStore')->store('zoneId', request('zone_id'));
            }
        }
    }
}
