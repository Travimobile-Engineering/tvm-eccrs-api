<?php

namespace App\Services;

use App\Models\Trip;
use App\Models\Vehicle;
use App\Traits\HttpResponse;
use App\Models\TransitCompany;
use App\Http\Resources\TripResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\VehicleResource;
use App\Http\Resources\TransportResource;

class TransportService
{
    use HttpResponse;

    public function getCompanies(){
        $companies = TransitCompany::with(['union', 'unionState', 'vehicles'])->paginate(25);
        return TransportResource::collection($companies);
    }
    public function getCompanyDetails($id){
        $company = TransitCompany::with( 'bookings', 'drivers.driver')->findOrFail($id);
        return $this->success(new TransportResource($company));
    }

    public function getDrivers($id){
        $company = TransitCompany::with(['drivers' => fn($q) => $q->with('union', 'documents')])->findOrFail( $id);
        return UserResource::collection($company->drivers);
    }

    public function getVehicles(){
        $vehicles = Vehicle::with('brand', 'driver.documents', 'company')->where('company_id', request()->id)->paginate(25);
        return VehicleResource::collection($vehicles);
    }

    public function getVehicle($id){
        $vehicle = Vehicle::with('brand', 'driver.documents', 'company')->findOrFail($id);
        return $this->success(new VehicleResource($vehicle));
    }

    public function getTrips($id, $status = null){
        $trips = Trip::with(['transitCompany', 'departureCity.state', 'destinationCity.state', 'vehicle' => fn($q) => $q->with('driver', 'brand')])->where('transit_company_id', $id);
            if($status){ $trips->where('status', $status); }
        return TripResource::collection($trips->paginate(25));
        

    }
}
