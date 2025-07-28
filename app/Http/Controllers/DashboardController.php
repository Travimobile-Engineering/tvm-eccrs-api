<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Dashboard\IncidentService;
use App\Services\Dashboard\TransportService;
use App\Services\Dashboard\WatchlistService;

class DashboardController extends Controller
{

    public function __construct(
        protected WatchlistService $watchlistService,
        protected TransportService $transportService,
        protected IncidentService $incidentService
    ){}

    public function overview(){
        return $this->watchlistService->overview();
    }

    public function list(){
        return $this->watchlistService->list();
    }

    public function getRecord($id){
        return $this->watchlistService->getRecord($id);
    }

    public function getTransportData(){
        return $this->transportService->getTransportData();
    }

    public function getIncidentData(){
        return $this->incidentService->getData();
    }

    public function getIncidentDetail($id){
        return $this->incidentService->getIncidentDetail($id);
    }
}
