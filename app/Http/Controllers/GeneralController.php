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
}
