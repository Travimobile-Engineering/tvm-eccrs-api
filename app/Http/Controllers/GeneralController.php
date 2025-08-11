<?php

namespace App\Http\Controllers;

use App\Services\GeneralService;

class GeneralController extends Controller
{
    public function __construct(
        protected GeneralService $service,
    ) {}

    public function getStates()
    {
        return $this->service->getStates();
    }

    public function getZones()
    {
        return $this->service->getZones();
    }

    public function getTransitCompanyUnions()
    {
        return $this->service->getTransitCompanyUnions();
    }
}
