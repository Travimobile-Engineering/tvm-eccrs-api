<?php

namespace App\Http\Controllers;

use App\Services\TransportService;

class TransportController extends Controller
{
    public function __construct(
        protected TransportService $service
    ) {}

    public function getCompanies()
    {
        return $this->service->getCompanies();
    }

    public function getCompanyDetails($id)
    {
        return $this->service->getCompanyDetails($id);
    }

    public function getDriver($id)
    {
        return $this->service->getDrivers($id);
    }

    public function getVehicles()
    {
        return $this->service->getVehicles();
    }

    public function getVehicle($id)
    {
        return $this->service->getVehicle($id);
    }

    public function getTrips($id, $status = null)
    {
        return $this->service->getTrips($id, $status);
    }

    public function getStats()
    {
        return $this->service->getStats();
    }

    public function getZoneData($zone = null)
    {
        return $this->service->getZoneData($zone);
    }
}
