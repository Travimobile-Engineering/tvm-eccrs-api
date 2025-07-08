<?php

namespace App\Services;

use App\Models\State;
use App\Models\Zone;
use App\Traits\HttpResponse;

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
}
