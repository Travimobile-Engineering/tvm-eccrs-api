<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WatchlistService;

class WatchlistController extends Controller
{
    public function __construct(
        protected WatchlistService $service
    ){}

    public function getWatchlistRecords()
    {
        return $this->service->getWatchlistRecords();
    }

    public function getWatchlistDetail($id)
    {
        return $this->service->getWatchlistDetail($id);
    }
}
