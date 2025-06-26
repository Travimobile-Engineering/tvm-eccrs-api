<?php

namespace App\Services;

use App\Models\Manifest;
use App\Traits\HttpResponse;

class ManifestService
{
    use HttpResponse;

    public function getManifests()
    {
        $type = request()->query('type');
        $month = request()->query('month');

        // only 'road' is implemented; return empty for others
        if ($type && $type !== 'road') {
            return $this->withPagination(
                new \Illuminate\Pagination\LengthAwarePaginator([], 0, 25),
                'No manifests found for this type.',
                200,
                [
                    'summary' => collect(['air', 'road', 'train', 'sea', 'hotel'])->mapWithKeys(fn ($t) => [$t => [
                        'manifest_count' => 0,
                        'passengers' => 0,
                    ]]),
                    'accessed_manifest' => [],
                ]
            );
        }

        $query = Manifest::with([
            'trip' => fn ($q) => $q->with([
                'departureCity.state',
                'destinationCity.state',
                'vehicle' => fn ($q) => $q->with('brand', 'driver.documents'),
                'bookings' => fn ($q) => $q->withCount('tripBookingPassengers')->with('user'),
            ]),
        ])->when($month, function ($q, $month) {
            $q->whereHas('trip', fn ($q) => $q
                ->whereMonth('departure_date', $month)
                ->whereYear('departure_date', now()->year)
            );
        });

        $manifests = $query->paginate(25);

        $manifestOverview = $manifests->getCollection()->map(function ($manifest) use ($type) {
            $passengerCount = $manifest->trip?->bookings->sum('trip_booking_passengers_count') ?? 0;

            return [
                'id' => $manifest->id,
                'manifest_code' => $manifest->trip?->uuid,
                'type' => $type ?? 'road',
                'location' => $manifest->trip
                    ? "{$manifest->trip->departureCity->name} to {$manifest->trip->destinationCity->name}"
                    : null,
                'total_passengers' => $passengerCount,
                'date' => "{$manifest->trip?->departure_date} {$manifest->trip?->departure_time}",
                'status' => $manifest->status,
            ];
        });

        $manifests->setCollection($manifestOverview);

        $allRoadManifests = Manifest::with([
            'trip.bookings' => fn ($q) => $q->withCount('tripBookingPassengers'),
        ])
            ->when($month, fn ($q, $month) => $q->whereHas('trip', fn ($q) => $q
                ->whereMonth('departure_date', $month)
                ->whereYear('departure_date', now()->year)
            )
            )
            ->get();

        $summary = collect(['air', 'road', 'train', 'sea', 'hotel'])->mapWithKeys(function ($type) use ($allRoadManifests) {
            if ($type === 'road') {
                $passengerCount = $allRoadManifests->sum(fn ($m) => $m->trip?->bookings->sum('trip_booking_passengers_count') ?? 0);

                return [$type => [
                    'manifest_count' => $allRoadManifests->count(),
                    'passengers' => $passengerCount,
                ]];
            }

            return [$type => [
                'manifest_count' => 0,
                'passengers' => 0,
            ]];
        });

        $extraMeta = [
            'summary' => $summary,
            'accessed_manifest' => [],
        ];

        return $this->withPagination($manifests, 'Manifest retrieved successfully', 200, $extraMeta);
    }

    public function getManifestDetail($id)
    {
        $manifest = Manifest::with([
            'trip' => fn ($q) => $q->with([
                'departureCity.state',
                'destinationCity.state',
                'vehicle' => fn ($q) => $q->with('brand', 'driver.documents'),
                'bookings.user',
                'bookings.tripBookingPassengers',
            ]),
        ])->findOrFail($id);

        return $this->success($manifest->toResource(), 'Manifest retrieved successfully');
    }
}
