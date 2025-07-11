<?php

namespace App\Http\Controllers;

use App\Services\IncidentService;

class IncidentController extends Controller
{
    public function __construct(
        protected IncidentService $service
    ) {}

    public function getIncidents()
    {
        return $this->service->getIncidents();
    }

    public function getIncidentStats()
    {
        return $this->service->getIncidentStats();
    }
}
