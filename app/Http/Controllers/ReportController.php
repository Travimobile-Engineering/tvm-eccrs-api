<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $service
    ) {}

    public function getReports(Request $request)
    {
        return $this->service->getReports($request);
    }
}
