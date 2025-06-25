<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePhoneNumberRequest;
use App\Http\Requests\CreateAccountRequest;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Requests\UpdateRoleRequest;
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

    public function createOrganization(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:1000',
        ]);

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

    public function updateOrganization(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:1000',
        ]);

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

    public function validatePhoneNumber(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|max:15',
            'code' => 'required|string|max:5',
        ]);

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

    public function suspendAccount(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer'],
            'reason' => 'required|string|max:255',
            'explanation' => 'nullable|string',
            'indefinite' => 'required|boolean',
            'end_date' => 'nullable|date|required_if:indefinite,false',
        ]);

        return $this->service->suspendAccount($request);
    }

    public function activateAccount(Request $request)
    {
        return $this->service->activateAccount($request);
    }
}
