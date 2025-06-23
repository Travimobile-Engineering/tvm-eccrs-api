<?php

namespace App\Services;

use App\Http\Resources\TransportResource;
use App\Http\Resources\UserResource;
use App\Models\TransitCompany;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Traits\HttpResponse;

class TransportService
{
    use HttpResponse;

    public function getCompanies()
    {
        $companies = TransitCompany::with(['union', 'unionState', 'vehicles'])
            ->when(request('search'), fn ($q, $search) => $q->where('name', 'like', "%$search%"));

        return $this->withPagination(TransportResource::collection($companies->paginate(25)), 'Companies retrieved successfully');
    }

    public function getCompanyDetails($id)
    {
        $company = TransitCompany::with(['bookings', 'drivers', 'activeTrips'])
            ->findOrFail($id);

        return $this->success(new TransportResource($company), 'Company retrieved successfully');
    }

    public function getDrivers($id)
    {
        $company = TransitCompany::with([
            'drivers' => function ($q) {
                return $q->with(['union', 'documents'])
                    ->when(request('search'), fn ($q, $search) => $q->search($search));
            },
        ])->findOrFail($id);

        return $this->success(UserResource::collection($company->drivers), 'Drivers retrieved successfully');
    }

    public function getVehicles()
    {
        $vehicles = Vehicle::with(['brand', 'driver.documents', 'company'])
            ->where('company_id', request()->id)
            ->when(request('search'), fn ($q, $search) => $q->where('plate_no', $search))
            ->paginate(25);

        return $this->withPagination($vehicles->paginate(25)->toResourceCollection(), 'Vehicles retrieved successfully');
    }

    public function getVehicle($id)
    {
        $vehicle = Vehicle::with(['brand', 'driver.documents', 'company'])->findOrFail($id);

        return $this->success($vehicle->toResource(), 'Vehicle retrieved successfully');
    }

    public function getTrips($id, $status = null)
    {
        $trips = Trip::with([
            'transitCompany',
            'mainifest',
            'departureCity' => function ($q) {
                $q->with('state')
                    ->when(request('search'), function ($q, $search) {
                        $q->where('name', 'like', "%$search%");
                    });
            },
            'destinationCity' => function ($q) {
                $q->with('state')
                    ->when(request('search'), function ($q, $search) {
                        $q->where('name', 'like', "%$search%");
                    });
            },
            'vehicle' => fn ($q) => $q->with('driver', 'brand'),
        ])
            ->where('transit_company_id', $id)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->paginate(25);

        return $this->withPagination($trips->toResourceCollection(), 'Trips retrieved successfully');
    }
}
