<?php

namespace App\Services;

use App\Enums\Zones;
use App\Http\Resources\TransportResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\ZoneDataResource;
use App\Models\State;
use App\Models\TransitCompany;
use App\Models\Trip;
use App\Models\TripBooking;
use App\Models\Vehicle;
use App\Traits\HttpResponse;
use App\Traits\TransportServiceTrait;

class TransportService
{
    use HttpResponse, TransportServiceTrait;

    public function getCompanies()
    {
        $companies = TransitCompany::with(['union', 'unionState', 'vehicles'])
            ->when(request('search'), fn ($q, $search) => $q->where('name', 'like', "%$search%"));

        return $this->withPagination(TransportResource::collection($companies->paginate(25)), 'Companies retrieved successfully');
    }

    public function getCompanyDetails($id)
    {
        $company = TransitCompany::with(['bookings', 'drivers', 'activeTrips'])
            ->findOrFail($id);

        return $this->success(new TransportResource($company), 'Company retrieved successfully');
    }

    public function getDrivers($id)
    {
        $company = TransitCompany::with([
            'drivers' => function ($q) {
                return $q->with(['union', 'documents'])
                    ->when(request('search'), fn ($q, $search) => $q->search($search));
            },
        ])->findOrFail($id);

        return $this->success(UserResource::collection($company->drivers), 'Drivers retrieved successfully');
    }

    public function getVehicles()
    {
        $vehicles = Vehicle::with(['brand', 'driver.documents', 'company'])
            ->where('company_id', request()->id)
            ->when(request('search'), fn ($q, $search) => $q->where('plate_no', $search))
            ->paginate(25);

        return $this->withPagination($vehicles->paginate(25)->toResourceCollection(), 'Vehicles retrieved successfully');
    }

    public function getVehicle($id)
    {
        $vehicle = Vehicle::with(['brand', 'driver.documents', 'company'])->findOrFail($id);

        return $this->success($vehicle->toResource(), 'Vehicle retrieved successfully');
    }

    public function getTrips($id, $status = null)
    {
        $trips = Trip::with([
            'transitCompany',
            'manifest',
            'departureCity' => function ($q) {
                $q->with('state')
                    ->when(request('search'), function ($q, $search) {
                        $q->where('name', 'like', "%$search%");
                    });
            },
            'destinationCity' => function ($q) {
                $q->with('state')
                    ->when(request('search'), function ($q, $search) {
                        $q->where('name', 'like', "%$search%");
                    });
            },
            'vehicle' => fn ($q) => $q->with('driver', 'brand'),
        ])
            ->where('transit_company_id', $id)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->paginate(25);

        return $this->withPagination($trips->toResourceCollection(), 'Trips retrieved successfully');
    }

    public function getStats()
    {
        $startLastMonth = now()->subMonth()->startOfMonth();
        $endLastMonth = now()->subMonth()->endOfMonth();
        $startThisMonth = now()->startOfMonth();
        $today = now()->startOfDay();

        $allBookings = TripBooking::with(['travellingWith', 'trip' => fn ($q) => $q->with('departureState', 'destinationState')])->get();
        $thisMonthBookings = $allBookings->filter(function ($booking) use ($startThisMonth, $today) {
            return $booking->created_at >= $startThisMonth && $booking->created_at <= $today;
        });
        $lastMonthBookings = $allBookings->filter(function ($booking) use ($startLastMonth, $endLastMonth) {
            return $booking->created_at >= $startLastMonth && $booking->created_at <= $endLastMonth;
        });

        $totalCancelledBookingThisMonth = $this->getTotalCancelledBookings($thisMonthBookings);
        $totalconfirmedBookingThisMonth = $this->getTotalConfirmedBookings($thisMonthBookings);
        $totalUnconfirmedBookingThisMonth = $this->getTotalUnconfirmedBookings($thisMonthBookings);

        $passengersCountLast = $this->getTotalBookings($lastMonthBookings);
        $passengersCountThis = $this->getTotalBookings($thisMonthBookings);

        $roadPassengersCountLast = $this->getTotalBookings($lastMonthBookings->filter(fn ($booking) => $booking->trip?->means == 'road'));
        $roadPassengersCountThis = $this->getTotalBookings($thisMonthBookings->filter(fn ($booking) => $booking->trip?->means == 'road'));

        $passengersTransported = collect();
        $pastTwelvethMonth = now()->subMonths(12)->startOfMonth()->format('Y-m-d');
        $bookings = $allBookings->filter(function ($booking) use ($pastTwelvethMonth) {
            return $booking->created_at >= $pastTwelvethMonth && $booking->created_at <= now();
        });

        for ($i = 0; $i < 12; $i++) {
            $month = now()->copy()->subMonths($i);

            $passengersTransported->push((object) [
                $month->monthName => [
                    'road' => $bookings->filter(function ($booking) use ($month) {
                        return $booking->trip?->means == 'road' && ($booking->created_at >= $month->startOfMonth() && $booking->created_at <= $month->endOfMonth());
                    })->count(),
                    'year' => $month->year,

                ],
            ]);
        }

        return $this->success([
            'passengers' => [
                'total' => $this->getTotalBookings($allBookings),
                'percentageDiff' => calculatePercentageDifference($passengersCountLast, $passengersCountThis),
            ],
            'air' => [
                'total' => null,
                'percentageDiff' => null,
            ],
            'road' => [
                'total' => $roadPassengersCountThis,
                'percentageDiff' => calculatePercentageDifference($roadPassengersCountLast, $roadPassengersCountThis),
            ],
            'train' => [
                'total' => null,
                'percentageDiff' => null,
            ],
            'sea' => [
                'total' => null,
                'percentageDiff' => null,
            ],
            'passengers_transported' => $passengersTransported,
            'route_breakdown' => [
                'lagos_abuja' => $allBookings->filter(function ($booking) {
                    return $booking->trip?->departureState->name == 'Lagos' && $booking->trip?->destinationState->name == 'FCT';
                })->count(),
                'rivers_lagos' => $allBookings->filter(function ($booking) {
                    return $booking->trip?->departureState->name == 'Rivers' && $booking->trip?->destinationState->name == 'Lagos';
                })->count(),
                'portharcourt_enugu' => $allBookings->filter(function ($booking) {
                    return $booking->trip?->departureState->name == 'Port Harcourt' && $booking->trip?->destinationState->name == 'Enugu';
                })->count(),
            ],
            'passenger_booking_overview' => [
                'total' => $passengersCountThis,
                'checkins' => $passengersCountThis > 0 ? ($totalconfirmedBookingThisMonth / $passengersCountThis) * 100 : 0,
                'awaiting_checkin' => $passengersCountThis > 0 ? ($totalUnconfirmedBookingThisMonth / $passengersCountThis) * 100 : 0,
                'cancelled' => $passengersCountThis > 0 ? ($totalCancelledBookingThisMonth / $passengersCountThis) * 100 : 0,
            ],
        ], 'Stats retrieved successfully');
    }

    public function getZoneData($zone)
    {
        $states = State::pluck('name')->toArray();
        $trips = Trip::with('departureState', 'destinationState', 'bookings')
            ->when(request('mode'), fn ($q, $mode) => $q->where('means', $mode))
            ->when($zone && ! request('state') && ! request('search'), function ($query) use ($zone, &$states) {

                $states = Zones::tryFrom($zone)?->states();
                $query->where(function ($query) use ($states) {
                    $query->whereHas('departureState', function ($query) use ($states) {
                        return $query->whereIn('states.name', $states);
                    })
                        ->orWhereHas('destinationState', function ($query) use ($states) {
                            return $query->whereIn('states.name', $states);
                        });
                });
            })
            ->when(request('state') && ! request('search'), function ($query) use (&$states) {

                $states = request('state');
                $query->where(function ($query) use ($states) {
                    $query->whereHas('departureState', function ($query) use ($states) {
                        return $query->whereIn('states.name', $states);
                    })
                        ->orWhereHas('destinationState', function ($query) use ($states) {
                            return $query->whereIn('states.name', $states);
                        });
                });
            })
            ->when(request('search'), function ($query, $search) use (&$states) {

                $states = request('search');
                $query->where(function ($query) use ($search) {
                    $query->whereHas('departureState', function ($query) use ($search) {
                        return $query->where('states.name', 'like', "%$search%");
                    })
                        ->orWhereHas('destinationState', function ($query) use ($search) {
                            return $query->where('states.name', 'like', "%$search%");
                        });
                });
            })
            ->between(now()->startOfMonth(), now())
            ->selectRaw('id, CONCAT(departure, destination) as route, departure, destination, means, COUNT(*) as trips_count')
            ->groupBy('route', 'id', 'departure', 'destination', 'means')
            ->orderBy('trips_count', 'desc');

        $vars = $this->setInboundOutboundData($states);

        return $this->withPagination(ZoneDataResource::collection($trips->paginate()),
            'Zone data retrieved successfully',
            200,
            [
                'inbound_passengers' => [
                    'total' => $vars->inboundData->total,
                    'road' => $vars->inboundData->road,
                    'air' => null,
                    'sea' => null,
                    'rail' => null,
                    'percentageDiff' => $vars->inboundPercentageDiff,
                ],
                'outbound_passengers' => [
                    'total' => $vars->outboundData->total,
                    'road' => $vars->outboundData->road,
                    'air' => null,
                    'sea' => null,
                    'rail' => null,
                    'percentageDiff' => $vars->inboundPercentageDiff,
                ],
                'most_active_departure_state' => $trips->first()?->departureState->name,
                'most_active_destination_state' => $trips->first()?->destinationState->name,
                'top_mode' => $trips->first()?->means,
            ]
        );
    }
}
