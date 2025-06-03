<?php

namespace App\Http\Controllers;

use App\Services\TransportService;
use Illuminate\Http\Request;

class TransportController extends Controller
{
    public function __construct(
        protected TransportService $service
    ){}

    public function getCompanies(){
        return $this->service->getCompanies();
    }

    public function getCompanyDetails($id){
        return $this->service->getCompanyDetails($id);
    }
}
