<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ChangePhoneNumberRequest;
use App\Http\Requests\CreateAccountRequest;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\SuspendAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Requests\ValidatePhoneNumberRequest;
use App\Services\SettingsService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(
        protected SettingsService $service,
    ) {}

    public function createRole(CreateRoleRequest $request)
    {
        $validated = $request->validated();

        return $this->service->createRole($validated);
    }

    public function getRoles()
    {
        return $this->service->getRoles();
    }

    public function getRole($id)
    {
        return $this->service->getRole($id);
    }

    public function updateRole(UpdateRoleRequest $request, $id)
    {
        $validated = $request->validated();

        return $this->service->updateRole($validated, $id);
    }

    public function deleteRole($id)
    {
        return $this->service->deleteRole($id);
    }

    public function getPermissions()
    {
        return $this->service->getPermissions();
    }

    public function createOrganization(OrganizationRequest $request)
    {
        return $this->service->createOrganization($request);
    }

    public function getOrganizations()
    {
        return $this->service->getOrganizations();
    }

    public function getOrganization($id)
    {
        return $this->service->getOrganization($id);
    }

    public function updateOrganization(OrganizationRequest $request, $id)
    {
        return $this->service->updateOrganization($request, $id);
    }

    public function deleteOrganization($id)
    {
        return $this->service->deleteOrganization($id);
    }

    public function getProfile($userId)
    {
        return $this->service->getProfile($userId);
    }

    public function changePhoneNumber(ChangePhoneNumberRequest $request)
    {
        return $this->service->changePhoneNumber($request);
    }

    public function validatePhoneNumber(ValidatePhoneNumberRequest $request)
    {
        return $this->service->validatePhoneNumber($request);
    }

    public function createAccount(CreateAccountRequest $request)
    {
        return $this->service->createAccount($request);
    }

    public function getAccounts()
    {
        return $this->service->getAccounts();
    }

    public function getAccount($id)
    {
        return $this->service->getAccount($id);
    }

    public function updateAccount(UpdateAccountRequest $request, $id)
    {
        return $this->service->updateAccount($request, $id);
    }

    public function deleteAccount($id)
    {
        return $this->service->deleteAccount($id);
    }

    public function suspendAccount(SuspendAccountRequest $request)
    {
        return $this->service->suspendAccount($request);
    }

    public function activateAccount(Request $request)
    {
        return $this->service->activateAccount($request);
    }

    public function changePasword(ChangePasswordRequest $request)
    {
        return $this->service->changePassword($request);
    }
}
