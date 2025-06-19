<?php

namespace App\Http\Controllers;

use App\Services\UserService;

class UserController extends Controller
{
    public function __construct(
        protected UserService $service,
    ) {}

    public function getTravellers()
    {
        return $this->service->getTravellers();
    }

    public function getUserDetail($user_id)
    {
        return $this->service->getUserDetail($user_id);
    }

    public function getAgents()
    {
        return $this->service->getAgents();
    }

    public function getDrivers()
    {
        return $this->service->getDrivers();
    }

    public function stats()
    {
        return $this->service->stats();
    }

    public function statActivities()
    {
        return $this->service->statActivities();
    }

    public function getStateActivities()
    {
        return $this->service->getStateActivities();
    }
}
