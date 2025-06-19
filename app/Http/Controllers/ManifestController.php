<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ManifestService;

class ManifestController extends Controller
{
    public function __construct(
        protected ManifestService $service
    ){}

    public function getManifestDetail($id){
        return $this->service->getManifestDetail($id);
    }
}
