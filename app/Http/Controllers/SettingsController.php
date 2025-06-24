<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePhoneNumberRequest;
use App\Http\Requests\CreateRoleRequest;
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
}
