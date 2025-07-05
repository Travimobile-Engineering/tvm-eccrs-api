<?php

namespace App\Services;

use App\Enums\Platform;
use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Http\Resources\AccountResource;
use App\Http\Resources\ProfileResource;
use App\Libraries\Utility;
use App\Models\AuthUser;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\Role;
use App\Models\State;
use App\Models\Suspension;
use App\Models\User;
use App\Models\Zone;
use App\Traits\HttpResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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

        if ($role->users()->exists()) {
            return $this->error(null, 'Cannot delete role. One or more users are assigned to this role.', 400);
        }

        $role->delete();

        return $this->success(null, 'Role deleted successfully');
    }

    public function getPermissions()
    {
        $permissions = Permission::select('id', 'name', 'group')
            ->get();

        if ($permissions->isEmpty()) {
            return $this->error(null, 'No permissions found', 404);
        }

        $grouped = $permissions->groupBy('group')->map(function ($items) {
            return $items->pluck('name')->all();
        });

        return $this->success($grouped->all(), 'Permissions retrieved successfully');
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
        $organizations = Organization::select('id', 'name', 'address')
            ->get()
            ->map(function ($org) {
                $org->has_users = $org->users()->exists();

                return $org;
            });

        if ($organizations->isEmpty()) {
            return $this->error(null, 'No organizations found', 404);
        }

        return $this->success($organizations, 'Organizations retrieved successfully');
    }

    public function getOrganization($id)
    {
        $organization = Organization::select('id', 'name', 'address')
            ->find($id);

        if (! $organization) {
            return $this->error(null, 'Organization not found', 404);
        }

        $organization->has_users = $organization->users()->exists();

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
        $organization = Organization::with('users')->find($id);

        if (! $organization) {
            return $this->error(null, 'Organization not found', 404);
        }

        if ($organization->users()->exists()) {
            return $this->error(null, 'Cannot delete organization. One or more users are assigned to this organization.', 400);
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

    public function createAccount($request)
    {
        try {
            DB::beginTransaction();

            $name = $request->name;
            $firstName = explode(' ', $name)[0];
            $lastName = explode(' ', $name)[1];
            $nin = Utility::encrypt($request->nin, config('security.encoding_key'));

            $uniqueId = generateUniqueNumber('users', 'unique_id', 9);

            $user = AuthUser::create([
                'unique_id' => $uniqueId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $request->email,
                'email_verified' => true,
                'email_verified_at' => now(),
                'phone_number' => formatPhoneNumber($request->phone_number),
                'nin' => $nin,
                'password' => bcrypt($request->password),
                'user_category' => UserType::SUPER_ADMIN->value,
                'organization_id' => $request->organization_id,
                'zone_id' => $request->zone_id,
                'state_id' => $request->state_id,
                'verification_code' => 0,
                'login_enabled' => $request->login_enabled,
                'platform' => Platform::ECCRS->value,
                'status' => UserStatus::ACTIVE->value,
            ]);

            $role = Role::find($request->role_id);
            $user->assignRole($role);

            DB::commit();

            return $this->success($user, 'Account created successfully', 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            return $this->error(null, $th->getMessage(), 400);
        }
    }

    public function getAccounts()
    {
        $zoneIds = collect();
        if ($zone = request('zone')) {
            $zoneIds = Zone::where('name', $zone)->pluck('id');
        }

        $stateIds = collect();
        if ($state = request('state')) {
            $stateIds = State::where('name', $state)->pluck('id');
        }

        $accounts = AuthUser::where('platform', Platform::ECCRS->value)
            ->with(['roles', 'zoneModel', 'stateModel', 'organization'])
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"])
                        ->orWhere('nin', 'like', "%{$search}%")
                        ->orWhere('unique_id', 'like', "%{$search}%");
                });
            })
            ->when(request('role'), fn ($q, $role) => $q->whereHas('roles', fn ($qr) => $qr->where('name', $role))
            )
            ->when($zoneIds->isNotEmpty(), fn ($q) => $q->whereIn('zone_id', $zoneIds)
            )
            ->when($stateIds->isNotEmpty(), fn ($q) => $q->whereIn('state_id', $stateIds)
            )
            ->when(request('status'), fn ($q, $status) => $q->where('status', $status)
            )
            ->paginate(25);

        $data = AccountResource::collection($accounts);

        $regionStats = \App\Models\Zone::select(['id', 'name'])->get()->map(function ($zone) {
            $count = AuthUser::where('platform', Platform::ECCRS->value)
                ->where('zone_id', $zone->id)
                ->count();

            return [
                'region' => $zone->name,
                'total' => $count,
            ];
        });

        $extraMeta = [
            'regions' => $regionStats,
        ];

        return $this->withPagination($data, 'Accounts retrieved successfully', 200, $extraMeta);
    }

    public function getAccount($id)
    {
        $account = AuthUser::where('id', $id)
            ->where('platform', Platform::ECCRS->value)
            ->with(['roles', 'zoneModel', 'stateModel', 'organization'])
            ->first();

        if (! $account) {
            return $this->error(null, 'Account not found', 404);
        }

        return $this->success(new AccountResource($account), 'Account retrieved successfully', 200);
    }

    public function updateAccount($request, $id)
    {
        $account = AuthUser::where('id', $id)
            ->where('platform', Platform::ECCRS->value)
            ->first();

        if (! $account) {
            return $this->error(null, 'Account not found', 404);
        }

        $name = $request->name;
        $firstName = explode(' ', $name)[0];
        $lastName = explode(' ', $name)[1];
        $nin = Utility::encrypt($request->nin, config('security.encoding_key'));

        $account->update([
            'first_name' => $firstName ?? $account->first_name,
            'last_name' => $lastName ?? $account->last_name,
            'email' => $request->email ?? $account->email,
            'phone_number' => formatPhoneNumber($request->phone_number) ?? $account->phone_number,
            'nin' => $nin ?? $account->nin,
            'organization_id' => $request->organization_id ?? $account->organization_id,
            'zone_id' => $request->zone_id ?? $account->zone_id,
            'state_id' => $request->state_id ?? $account->state_id,
        ]);

        $role = Role::find($request->role_id);
        $account->syncRoles([$role->id]);

        return $this->success($account, 'Account updated successfully');
    }

    public function deleteAccount($id)
    {
        $account = AuthUser::where('id', $id)
            ->where('platform', Platform::ECCRS->value)
            ->first();

        if (! $account) {
            return $this->error(null, 'Account not found', 404);
        }

        $account->delete();

        return $this->success(null, 'Account deleted successfully');
    }

    public function suspendAccount($request)
    {
        if (auth()->id() == $request->user_id) {
            return $this->error(null, 'You cannot suspend yourself', 403);
        }

        $user = AuthUser::findOrFail($request->user_id);

        $activeSuspension = $user->suspensions()
            ->whereNull('lifted_at')
            ->where(function ($q) {
                $q->where('indefinite', true)
                    ->orWhere('end_date', '>', now());
            })
            ->exists();

        if ($activeSuspension) {
            return $this->error(null, 'Account is already suspended', 400);
        }

        Suspension::create([
            'user_id' => $user->id,
            'suspended_by' => auth()->id(),
            'reason' => $request->reason,
            'explanation' => $request->explanation,
            'indefinite' => $request->indefinite,
            'end_date' => $request->indefinite ? null : $request->end_date,
        ]);

        $user->update([
            'status' => UserStatus::SUSPENDED->value,
            'suspended_until' => $request->indefinite ? null : $request->end_date,
        ]);

        return $this->success(null, 'Account suspended successfully');
    }

    public function activateAccount($request)
    {
        $user = AuthUser::findOrFail($request->user_id);

        if ($user->latestSuspension && ! $user->latestSuspension->lifted_at) {
            $user->latestSuspension->update(['lifted_at' => now()]);
        }

        $user->update([
            'status' => UserStatus::ACTIVE->value,
            'suspended_until' => null,
        ]);

        return $this->success(null, 'Account activated successfully');
    }

    public function changePassword($request)
    {
        $user = AuthUser::findOrFail($request->user_id);

        if (! Hash::check($request->old_password, $user->password)) {
            return $this->error(null, 'Current password is incorrect', 400);
        }

        $user->update([
            'password' => bcrypt($request->new_password),
        ]);

        return $this->success(null, 'Password changed successfully');
    }
}
