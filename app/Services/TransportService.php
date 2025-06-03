<?php

namespace App\Services;

use App\Http\Resources\TransportResource;
use App\Models\TransitCompany;
use App\Traits\HttpResponse;

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
}
