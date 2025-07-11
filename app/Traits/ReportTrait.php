<?php

namespace App\Traits;

use App\Exports\ManifestReportExport;
use App\Models\Manifest;
use App\Models\Trip;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

trait ReportTrait
{
    // Export Manifest Report
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

    // Export Manifest Report to PDF / Excel / CSV
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

    // Get Hotel Report
    public function getHotelReport()
    {
        return [];
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

    public function getTransportReport($user, $request)
    {
        $trips = Trip::with([
            'departureCity',
            'destinationCity',
            'vehicle',
            'bookings',
            'bookings.tripBookingPassengers',
        ])
            ->filterByUserZone($user)
            ->filterByReport($request->only(['start_date', 'end_date', 'zone', 'state', 'from', 'to']))
            ->get();

        $reportData = [];

        foreach ($trips as $trip) {
            $route = "{$trip->departureCity->name} - {$trip->destinationCity->name}";
            $modeOfTransport = $request->get('data_type', 'road');

            $passengers = $trip->bookings->pluck('tripBookingPassengers')->flatten()->count();
            $totalBookings = $trip->bookings->count();
            $totalCheckIns = $trip->bookings
                ->pluck('tripBookingPassengers')
                ->flatten()
                ->where('on_seat', true)
                ->count();

            $occupancyRate = $passengers ? round(($totalCheckIns / $passengers) * 100, 2).'%' : '0%';

            $reportData[] = [
                'route' => $route,
                'mode_of_transport' => $modeOfTransport,
                'passengers' => $passengers,
                'trips' => $trips->count(),
                'bookings_vs_checkins' => "{$totalBookings} / {$totalCheckIns}",
                'occupancy_rate' => $occupancyRate,
            ];
        }

        $summary = collect($reportData)
            ->groupBy(fn ($item) => $item['route'].'-'.$item['mode_of_transport'])
            ->map(function ($group) {
                return [
                    'route' => $group->first()['route'],
                    'mode_of_transport' => $group->first()['mode_of_transport'],
                    'passengers' => $group->sum('passengers'),
                    'trips' => $group->count(),
                    'bookings_vs_checkins' => $group->reduce(function ($carry, $item) {
                        [$b1, $c1] = explode(' / ', $carry);
                        [$b2, $c2] = explode(' / ', $item['bookings_vs_checkins']);

                        return (intval($b1) + intval($b2)).' / '.(intval($c1) + intval($c2));
                    }, '0 / 0'),
                    'occupancy_rate' => $group->first()['occupancy_rate'],
                ];
            })->values();

        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 15);
        $total = $summary->count();

        $paginated = new LengthAwarePaginator(
            $summary->forPage($page, $perPage),
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return $this->withPagination($paginated, 'Transport report retrieved successfully');
    }

    private function exportManifestReportToPdf($data)
    {
        $pdf = Pdf::loadView('exports.manifest_report', ['data' => $data]);

        return $pdf->download('manifest_report.pdf');
    }

    private function exportManifestReportToExcel($data)
    {
        return Excel::download(new ManifestReportExport($data), 'manifest_report.xlsx');
    }

    private function exportManifestReportToCsv($data)
    {
        return Excel::download(new ManifestReportExport($data), 'manifest_report.csv', ExcelFormat::CSV);
    }
}
