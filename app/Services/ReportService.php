<?php

namespace App\Services;

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
}
