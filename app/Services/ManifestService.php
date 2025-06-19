<?php

namespace App\Services;

use App\Models\Manifest;
use App\Traits\HttpResponse;

class ManifestService
{
    use HttpResponse;

    public function getManifestDetail($id){
        $manifest = Manifest::with([
            'trip' => fn($q)=>$q->with([
                    'departureCity.state',
                    'destinationCity.state',
                    'vehicle' => fn($q) => $q->with('brand', 'driver.documents'),
                    'bookings.user',
                ])
            ])->findOrFail($id);
        return $this->success($manifest->toResource(), "Manifest retrieved successfully");
    }
}
