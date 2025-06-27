<?php

namespace App\Services;

use App\Enums\Zones;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Models\TripBooking;
use App\Traits\HttpResponse;
use App\Models\TransitCompany;
use App\Http\Resources\UserResource;
use App\Http\Resources\ZoneDataResource;
use App\Http\Resources\TransportResource;

class TransportService
{
    use HttpResponse;

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

        $totalCancelledBookingThisMonth = TripBooking::createdBetween($startThisMonth, $today)->where('status', 0)->count();
        $totalconfirmedBookingThisMonth = TripBooking::createdBetween($startThisMonth, $today)->where('confirmed', 1)->count();
        $totalUnconfirmedBookingThisMonth = TripBooking::createdBetween($startThisMonth, $today)->where('confirmed', 0)->count();

        $passengersCountLast = TripBooking::createdBetween($startLastMonth, $endLastMonth)->count();
        $passengersCountThis = TripBooking::createdBetween($startThisMonth, $today)->count();
        
        $roadPassengersCountLast = TripBooking::createdBetween($startLastMonth, $endLastMonth)
            ->whereHas('trip', fn($q) => $q->where('means', 'road'))
            ->count();
        $roadPassengersCountThis = TripBooking::createdBetween($startThisMonth, $today)
            ->whereHas('trip', fn($q) => $q->where('means', 'road'))
            ->count();

        $airPassengersCountLast = TripBooking::createdBetween($startLastMonth, $endLastMonth)
            ->whereHas('trip', fn($q) => $q->where('means', 'air'))
            ->count();
        $airPassengersCountThis = TripBooking::createdBetween($startThisMonth, $today)
            ->whereHas('trip', fn($q) => $q->where('means', 'air'))
            ->count();

        $trainPassengersCountLast = TripBooking::createdBetween($startLastMonth, $endLastMonth)
            ->whereHas('trip', fn($q) => $q->where('means', 'train'))
            ->count();
        $trainPassengersCountThis = TripBooking::createdBetween($startThisMonth, $today)
            ->whereHas('trip', fn($q) => $q->where('means', 'train'))
            ->count();

        $seaPassengersCountLast = TripBooking::createdBetween($startLastMonth, $endLastMonth)
            ->whereHas('trip', fn($q) => $q->where('means', 'sea'))
            ->count();
        $seaPassengersCountThis = TripBooking::createdBetween($startThisMonth, $today)
            ->whereHas('trip', fn($q) => $q->where('means', 'sea'))
            ->count();

        $passengersTransported = collect();
        for($i=0;$i<12;$i++){

            $thisMonth = now()->month;
            $month =  now()->month( $thisMonth - $i);
            $bookings = TripBooking::createdBetween($month->startOfMonth()->format('Y-m-d'), $month->endOfMonth()->format('Y-m-d'));


            $passengersTransported->push((object)[
               $month->monthName => [
                    'road' => $bookings->whereHas('trip', fn($q) => $q->where('means', 'road'))->count(),
                    'sea' => $bookings->whereHas('trip', fn($q) => $q->where('means', 'sea'))->count(),
                    'rail' => $bookings->whereHas('trip', fn($q) => $q->where('means', 'rail'))->count(),
                    'air' => $bookings->whereHas('trip', fn($q) => $q->where('means', 'air'))->count(),
                    'year' => $month->year,
                    
               ]
            ]);
        }

        return $this->success([
            'passengers' => [
                'total' => $passengersCountThis,
                'percentageDiff' => calculatePercentageDifference($passengersCountLast, $passengersCountThis),
            ],
            'air' => [
                'total' => $airPassengersCountThis,
                'percentageDiff' => calculatePercentageDifference($airPassengersCountLast, $airPassengersCountThis),
            ],
            'road' => [
                'total' => $roadPassengersCountThis,
                'percentageDiff' => calculatePercentageDifference($roadPassengersCountLast, $roadPassengersCountThis),
            ],
            'train' => [
                'total' => $trainPassengersCountThis,
                'percentageDiff' => calculatePercentageDifference($trainPassengersCountLast, $trainPassengersCountThis),
            ],
            'sea' => [
                'total' => $seaPassengersCountThis,
                'percentageDiff' => calculatePercentageDifference($seaPassengersCountLast, $seaPassengersCountThis),
            ],
            'passengers_transported' => $passengersTransported,
            'route_breakdown' => [
                'lagos_abuja' => TripBooking::whereHas('trip', function($query){
                    $query->whereHas('departureState', function($q){
                        $q->where('states.name', 'Lagos');
                    })
                    ->whereHas('destinationState', function($q){
                        $q->where('states.name', 'FCT');
                    });
                })->count(),
                'rivers_lagos' => TripBooking::whereHas('trip', function($query){
                    $query->whereHas('departureState', function($q){
                        $q->where('states.name', 'Rivers');
                    })
                    ->whereHas('destinationState', function($q){
                        $q->where('states.name', 'Lagos');
                    });
                })->count(),
                'portharcourt_enugu' => TripBooking::whereHas('trip', function($query){
                    $query->whereHas('departureState', function($q){
                        $q->where('states.name', 'Port Harcourt');
                    })
                    ->whereHas('destinationState', function($q){
                        $q->where('states.name', 'Enugu');
                    });
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
        $vars = null;

        $trips = Trip::with('departureState', 'destinationState', 'bookings', 'confirmedBookings')
        ->when(request('mode'), fn($q, $mode) => $q->where('means', $mode))
        ->when($zone && !request('state') && !request('search'), function($query) use($zone, &$vars){
            
            $states = Zones::tryFrom($zone)?->states();
            $query->where(function($query) use($states){
                $query->whereHas('departureState', function($query) use($states){
                    return $query->whereIn('states.name', $states);
                })
                ->orWhereHas('destinationState', function($query) use($states){
                    return $query->whereIn('states.name', $states);
                });
            });

            $vars = $this->setInboundOutboundData([$states]);
        })
        ->when(request('state') && !request('search'), function($query) use(&$vars){
            
            $state = request('state');
            $query->where(function($query) use($state){
                $query->whereHas('departureState', function($query) use($state){
                    return $query->where('states.name', $state);
                })
                ->orWhereHas('destinationState', function($query) use($state){
                    return $query->where('states.name', $state);
                });
            });

            $vars = $this->setInboundOutboundData([$state]);
        })
        ->when(request('search'), function($query, $search) use(&$vars){
            $query->where(function($query) use($search){
                $query->whereHas('departureState', function($query) use($search){
                    return $query->where('states.name', 'like', "%$search%");
                })
                ->orWhereHas('destinationState', function($query) use($search){
                    return $query->where('states.name', 'like', "%$search%");
                });
            });

            $vars = $this->setInboundOutboundData([$search]);
        })
        ->between(now()->startOfMonth(), now())
        ->selectRaw('id, CONCAT(departure, destination) as route, departure, destination, means, COUNT(*) as trips_count')
        ->groupBy('route', 'id', 'departure', 'destination', 'means')
        ->orderBy('trips_count', 'desc');

        return $this->withPagination(ZoneDataResource::collection($trips->paginate())->additional([
            'additional' => [
                'inbound_passengers' => [
                    'total' => $vars['inbound_passengers_count'],
                    'percentageDiff' => $vars['inboundPercentageDiff'],
                ],
                'outbound_passengers' => [
                    'total' => $vars['outbound_passengers_count'],
                    'percentageDiff' => $vars['inboundPercentageDiff'],
                ],
                'most_active_departure_state' => $trips->first()?->departureState->name,
                'most_active_destination_state' => $trips->first()?->destinationState->name,
                'top_mode' => $trips->first()?->means,
            ]
        ]), 'Zone data retrieved successfully');
    }

    protected function getInboundPassengersCount(array $states, $from = null, $to = null){
        return Trip::whereHas('destinationState', fn($q) => $q->whereIn('states.name', $states))
        ->between($from ?? now()->startOfMonth(), $to ?? now())
        ->count();
    }

    protected function getOutboundPassengersCount(array $states, $from = null, $to = null){
        return Trip::whereHas('departureState', fn($q) => $q->whereIn('states.name', $states))
        ->between($from ?? now()->startOfMonth(), $to ?? now())
        ->count();
    }

    protected function setInboundOutboundData(array $states){
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();
        $inbound_passengers_count = $this->getInboundPassengersCount($states);
        $outbound_passengers_count = $this->getOutboundPassengersCount($states);
        $lastMonthInboundPassengersCount = $this->getInboundPassengersCount($states, $lastMonthStart, $lastMonthEnd);
        $lastMonthOutboundPassengersCount = $this->getOutboundPassengersCount($states, $lastMonthStart, $lastMonthEnd);

        return [
            'inbound_passengers_count' => $inbound_passengers_count,
            'outbound_passengers_count' => $outbound_passengers_count,
            'lastMonthInboundPassengersCount' => $lastMonthInboundPassengersCount,
            'lastMonthOutboundPassengersCount' => $lastMonthOutboundPassengersCount,
            'inboundPercentageDiff' => calculatePercentageDifference( $lastMonthInboundPassengersCount, $inbound_passengers_count),
            'outboundPercentageDiff' => calculatePercentageDifference($lastMonthOutboundPassengersCount, $outbound_passengers_count),
        ];
    }
}
