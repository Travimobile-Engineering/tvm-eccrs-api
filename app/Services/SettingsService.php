<?php

namespace App\Services;

use App\Http\Resources\ProfileResource;
use App\Models\AuthUser;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\User;
use App\Traits\HttpResponse;

class SettingsService
{
    use HttpResponse;

    public function createRole($request)
    {
        $role = User::createRole(
            $request['name'],
            $request['description'],
            $request['permissions']
        );

        return $this->success($role, 'Role created successfully', 201);
    }

    public function getRoles()
    {
        $roles = User::getRoles();

        if ($roles->isEmpty()) {
            return $this->error(null, 'No roles found', 404);
        }

        return $this->success($roles, 'Roles retrieved successfully');
    }

    public function getRole($id)
    {
        $role = User::getRole($id);

        if (! $role) {
            return $this->error(null, 'Role not found', 404);
        }

        return $this->success($role, 'Role retrieved successfully');
    }

    public function updateRole($request, $id)
    {
        $role = User::updateRole(
            $id,
            $request['name'],
            $request['description'],
            $request['permissions']
        );

        if (! $role) {
            return $this->error(null, 'Role not found', 404);
        }

        return $this->success($role, 'Role updated successfully');
    }

    public function deleteRole($id)
    {
        $role = User::getRole($id);

        if (! $role) {
            return $this->error(null, 'Role not found', 404);
        }

        $role->delete();

        return $this->success(null, 'Role deleted successfully');
    }

    public function getPermissions()
    {
        $permissions = Permission::select('id', 'name')->get();

        if ($permissions->isEmpty()) {
            return $this->error(null, 'No permissions found', 404);
        }

        return $this->success($permissions, 'Permissions retrieved successfully');
    }

    public function createOrganization($request)
    {
        Organization::create([
            'name' => $request->name,
            'address' => $request->address,
        ]);

        return $this->success(null, 'Organization created successfully', 201);
    }

    public function getOrganizations()
    {
        $organizations = Organization::select('id', 'name', 'address')->get();

        if ($organizations->isEmpty()) {
            return $this->error(null, 'No organizations found', 404);
        }

        return $this->success($organizations, 'Organizations retrieved successfully');
    }

    public function getOrganization($id)
    {
        $organization = Organization::select('id', 'name', 'address')->find($id);

        if (! $organization) {
            return $this->error(null, 'Organization not found', 404);
        }

        return $this->success($organization, 'Organization retrieved successfully');
    }

    public function updateOrganization($request, $id)
    {
        $organization = Organization::find($id);

        if (! $organization) {
            return $this->error(null, 'Organization not found', 404);
        }

        $organization->update([
            'name' => $request->name,
            'address' => $request->address,
        ]);

        return $this->success($organization, 'Organization updated successfully');
    }

    public function deleteOrganization($id)
    {
        $organization = Organization::find($id);

        if (! $organization) {
            return $this->error(null, 'Organization not found', 404);
        }

        $organization->delete();

        return $this->success(null, 'Organization deleted successfully');
    }

    public function getProfile($userId)
    {
        $user = AuthUser::with('roles')->find($userId);

        if (! $user) {
            return $this->error(null, 'User not found', 404);
        }

        $response = new ProfileResource($user);

        return $this->success($response, 'User profile retrieved successfully');
    }

    public function changePhoneNumber($request)
    {
        try {
            $phone = formatPhoneNumber($request->phone_number);

            $user = AuthUser::where('id', $request->user_id)
                ->where('phone_number', $phone)
                ->first();

            if (! $user) {
                return $this->error(null, 'User not found', 404);
            }

            $code = getCode();

            $user->update([
                'verification_code' => $code,
                'verification_code_expires_at' => now()->addMinutes(10),
            ]);

            sendSmS($phone, "Your Travi Verification Code is: $code. Valid for 10 mins. Do not share with anyone. Powered By Travi");

            return $this->success(null, 'Verification code has been sent to your phone number!');
        } catch (\Throwable $th) {
            return $this->error(null, $th->getMessage(), 400);
        }
    }

    public function validatePhoneNumber($request)
    {
        $user = AuthUser::where('id', $request->user_id)
            ->where('verification_code', $request->code)
            ->first();

        if (! $user) {
            return $this->error(null, 'Invalid verification code', 400);
        }

        if ($user->verification_code_expires_at < now()) {
            return $this->error(null, 'Verification code has expired', 400);
        }

        $user->update([
            'phone_number' => formatPhoneNumber($request->phone_number),
            'verification_code' => 0,
            'verification_code_expires_at' => null,
        ]);

        return $this->success(null, 'Phone number verified & updated successfully');
    }
}
