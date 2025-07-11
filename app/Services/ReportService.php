<?php

namespace App\Services;

use App\Exports\ManifestReportExport;
use App\Exports\TransportReportExport;
use App\Models\Manifest;
use App\Traits\HttpResponse;
use App\Traits\ReportTrait;

class ReportService
{
    use HttpResponse, ReportTrait;

    public function getReports($request)
    {
        $user = userAuth();

        $reportType = $request->get('report_type');
        $dataType = $request->get('data_type');

        return match ($reportType) {
            'manifest' => match ($dataType) {
                'hotel' => $this->getHotelReport(),
                default => $this->getManifestReport($user, $request, $dataType),
            },
            'transport' => $this->getTransportReport($user, $request),
            default => $this->error(null, 'Report type not found', 404),
        };
    }

    public function getReportDetail($id)
    {
        $user = userAuth();

        $manifest = Manifest::with([
            'trip' => fn ($q) => $q->with([
                'departureCity.state',
                'destinationCity.state',
                'vehicle' => fn ($q) => $q->with('brand', 'driver.documents'),
                'bookings.user',
                'bookings.tripBookingPassengers',
            ]),
        ])
            ->filterByUserZone($user)
            ->findOrFail($id);

        return $this->success($manifest->toResource(), 'Manifest retrieved successfully');
    }

    // Export Manifest Report to PDF / Excel / CSV
    public function exportReports($request)
    {
        $user = userAuth();
        $dataType = $request->post('data_type', 'road');
        $exportType = $request->post('export', 'csv');

        $data = match ($request->report_type) {
            'manifest' => $this->exportManifestReport($user, $request, $dataType),
            'transport' => $this->exportTransportReport($user, $request),
            default => $this->error(null, 'Report type not found', 404),
        };

        return match ($request->report_type) {
            'manifest' => match ($exportType) {
                'pdf' => $this->exportToPdf('exports.manifest_report', $data, 'manifest_report'),
                'excel' => $this->exportToExcel(new ManifestReportExport($data), 'manifest_report'),
                'csv' => $this->exportToCsv(new ManifestReportExport($data), 'manifest_report'),
                default => $this->error(null, 'Export type not found', 404),
            },
            'transport' => match ($exportType) {
                'pdf' => $this->exportToPdf('exports.transport_report', $data, 'transport_report'),
                'excel' => $this->exportToExcel(new TransportReportExport($data), 'transport_report'),
                'csv' => $this->exportToCsv(new TransportReportExport($data), 'transport_report'),
                default => $this->error(null, 'Export type not found', 404),
            }
        };
    }
}
