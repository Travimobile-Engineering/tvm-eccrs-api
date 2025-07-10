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
            default => $this->error(null, 'Report type not found', 404),
        };
    }

    public function getManifestReport($user, $request, $dataType)
    {
        $query = Manifest::with([
            'trip.bookings' => fn ($q) => $q->withCount('tripBookingPassengers'),
        ])
            ->filterByUserZone($user)
            ->filterByReport($request->only(['start_date', 'end_date', 'zone', 'state', 'from', 'to']));

        $manifests = $query->latest()->paginate(25);

        $data = $manifests->getCollection()->map(function ($manifest) use ($dataType) {
            $passengerCount = $manifest->trip?->bookings->sum('trip_booking_passengers_count') ?? 0;

            return [
                'id' => $manifest->id,
                'manifest_code' => $manifest->trip?->uuid,
                'type' => $dataType ?? 'road',
                'location' => $manifest->trip
                    ? "{$manifest->trip->departureCity->name} to {$manifest->trip->destinationCity->name}"
                    : null,
                'total_passengers' => $passengerCount,
                'date' => "{$manifest->trip?->departure_date} {$manifest->trip?->departure_time}",
                'status' => $manifest->status,
            ];
        });

        $manifests->setCollection($data);

        return $this->withPagination($manifests, 'Manifests retrieved successfully');
    }

    public function getHotelReport()
    {
        return [];
    }

    public function exportManifestReport($user, $request, $dataType)
    {
        $query = Manifest::with([
            'trip.bookings' => fn ($q) => $q->withCount('tripBookingPassengers'),
        ])
            ->filterByUserZone($user)
            ->filterByReport($request->only(['start_date', 'end_date', 'zone', 'state', 'from', 'to']));

        $manifests = $query->latest()->limit(5000)->get();

        return $manifests->map(function ($manifest) use ($dataType) {
            $passengerCount = $manifest->trip?->bookings->sum('trip_booking_passengers_count') ?? 0;

            return [
                'ID' => $manifest->id,
                'Manifest Code' => $manifest->trip?->uuid,
                'Type' => $dataType ?? 'road',
                'Location' => $manifest->trip
                    ? "{$manifest->trip->departureCity->name} to {$manifest->trip->destinationCity->name}"
                    : null,
                'Total Passengers' => $passengerCount,
                'Date' => "{$manifest->trip?->departure_date} {$manifest->trip?->departure_time}",
                'Status' => $manifest->status,
            ];
        });
    }

    public function exportReports($request)
    {
        $user = userAuth();
        $dataType = $request->post('data_type', 'road');
        $exportType = $request->post('export', 'csv');

        $data = match ($request->report_type) {
            'manifest' => $this->exportManifestReport($user, $request, $dataType),
            default => $this->error(null, 'Report type not found', 404),
        };

        return match ($exportType) {
            'pdf' => $this->exportManifestReportToPdf($data),
            'excel' => $this->exportManifestReportToExcel($data),
            'csv' => $this->exportManifestReportToCsv($data),
            default => $this->error(null, 'Export type not found', 404),
        };
    }
}
