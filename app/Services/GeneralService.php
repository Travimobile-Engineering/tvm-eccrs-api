<?php

namespace App\Services;

use App\Models\Zone;
use App\Models\State;
use App\Traits\HttpResponse;
use App\Models\TransitCompanyUnion;

class GeneralService
{
    use HttpResponse;

    public function getStates()
    {
        $states = State::select('id', 'name')->get();

        return $this->success($states, 'States retrieved successfully');
    }

    public function getZones()
    {
        $zones = Zone::select('id', 'name')->get();

        return $this->success($zones, 'Zones retrieved successfully');
    }

    public function getTransitCompanyUnions()
    {
        $unions = TransitCompanyUnion::select('id', 'name')->get();

        return $this->success($unions, 'Unions retrieved successfully');
    }
}
