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

    public function getReportDetail($id)
    {
        return $this->service->getReportDetail($id);
    }

    public function exportReports(Request $request)
    {
        $request->validate([
            'report_type' => ['required', 'string', 'in:manifest,hotel,transport'],
            'data_type' => ['required', 'string', 'in:hotel,road,air'],
            'export' => ['required', 'string', 'in:pdf,excel,csv'],
        ]);

        return $this->service->exportReports($request);
    }
}
