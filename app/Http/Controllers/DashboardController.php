<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Dashboard\WatchlistService;

class DashboardController extends Controller
{

    public function __construct(
        protected WatchlistService $service
    ){}

    public function overview(){
        return $this->service->overview();
    }

    public function list(){
        return $this->service->list();
    }

    public function getRecord($id){
        return $this->service->getRecord($id);
    }
}
