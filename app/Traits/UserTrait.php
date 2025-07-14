<?php

namespace App\Traits;

use App\Models\Role;
use App\Models\User;
use App\Models\TripBooking;
use App\Models\TransitCompany;

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

    protected function setZoneId($zoneId)
    {
        if(! empty(request()->header('zone_id'))){
            User::setZoneId($zoneId);
            TripBooking::setZoneId($zoneId);
            TransitCompany::setZoneId($zoneId);
        }
    }

    protected function sortColumn($sort){
        return explode(',', $sort)[0] ?? 'created_at';
    }

    protected function sortOrder($sort){
        return explode(',', $sort)[1] ?? 'desc';
    }
}
