<?php

namespace App\Services;

use App\Models\State;
use App\Traits\HttpResponse;

class GeneralService
{
    use HttpResponse;

    public function getStates()
    {
        $states = State::select('id', 'name')->get();

        return $this->success($states, 'States retrieved successfully');
    }
}
