<?php

namespace App\Services;

use App\Models\Manifest;
use App\Traits\HttpResponse;

class ReportService
{
    use HttpResponse;

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
}
