<?php

namespace App\Http\Controllers;

use App\Services\ManifestService;

class ManifestController extends Controller
{
    public function __construct(
        protected ManifestService $service
    ) {}

    public function getManifests()
    {
        return $this->service->getManifests();
    }

    public function getManifestDetail($id)
    {
        return $this->service->getManifestDetail($id);
    }
}
