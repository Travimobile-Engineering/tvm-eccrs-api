<?php

namespace App\Services;

use App\Enums\UserType;
use App\Enums\Zones;
use App\Http\Resources\UserResource;
use App\Models\State;
use App\Models\TransitCompany;
use App\Models\TripBooking;
use App\Models\User;
use App\Services\Actions\UserActionService;
use App\Traits\HttpResponse;

class UserService
{
    use HttpResponse;

    public function __construct(
        protected UserActionService $actionService,
    ) {}

    public function getTravellers()
    {
        $travellers = User::whereHas('tripBookings')
            ->when(request('search'), fn ($q, $search) => $q->search($search))
            ->paginate(25);

        return $this->withPagination(UserResource::collection($travellers), 'Travellers retrieved successfully');
    }

    public function getUserDetail($id)
    {
        $user = User::with([
            'vehicle.brand',
            'watchlists',
            'trips' => fn ($q) => $q->with(['manifest', 'bookings', 'departureCity.state', 'destinationCity.state']),
            'tripBookings.trip' => fn ($query) => $query->with(['transitCompany', 'departureCity.state', 'destinationCity.state']),
        ])->findOrFail($id);

        return $this->success($user->toResource(), 'User retrieved successfully');
    }

    public function getAgents()
    {
        $agents = User::isAgent()
            ->when(request('search'), fn ($q, $search) => $q->search($search)->orWhere('agent_id', $search))
            ->paginate(25);

        return $this->withPagination(UserResource::collection($agents), 'Agents retrieved successfully');
    }

    public function getDrivers()
    {
        $drivers = User::with(['documents', 'union'])
            ->where('user_category', UserType::DRIVER->value)
            ->when(request('search'), fn ($q, $search) => $q->search($search))
            ->whereHas('vehicle')
            ->paginate(25);

        return $this->withPagination(UserResource::collection($drivers), 'Drivers retrieved successfully');
    }

    public function stats()
    {
        $startLastMonth = now()->subMonth()->startOfMonth();
        $endLastMonth = now()->subMonth()->endOfMonth();
        $startThisMonth = now()->startOfMonth();
        $today = now()->startOfDay();

        $tripCountLast = TripBooking::createdBetween($startLastMonth, $endLastMonth)->count();
        $tripCountThis = TripBooking::createdBetween($startThisMonth, $today)->count();

        $agentCountLast = User::agentsBetween($startLastMonth, $endLastMonth)->count();
        $agentCountThis = User::agentsBetween($startThisMonth, $today)->count();

        $driverCountLast = User::driversBetween($startLastMonth, $endLastMonth)->count();
        $driverCountThis = User::driversBetween($startThisMonth, $today)->count();

        $companyCountLast = TransitCompany::signedUpBetween($startLastMonth, $endLastMonth)->count();
        $companyCountThis = TransitCompany::signedUpBetween($startThisMonth, $today)->count();

        $distribution = TransitCompany::countByType();

        $totalThisMonth = $tripCountThis + $agentCountThis + $driverCountThis + $companyCountThis;

        return $this->success([
            'travelers' => [
                'total' => $tripCountThis,
                'percentageDiff' => calculatePercentageDifference($tripCountLast, $tripCountThis),
            ],
            'agents' => [
                'total' => $agentCountThis,
                'percentageDiff' => calculatePercentageDifference($agentCountLast, $agentCountThis),
            ],
            'drivers' => [
                'total' => $driverCountThis,
                'percentageDiff' => calculatePercentageDifference($driverCountLast, $driverCountThis),
            ],
            'transport_companies' => [
                'total' => $companyCountThis,
                'percentageDiff' => calculatePercentageDifference($companyCountLast, $companyCountThis),
            ],
            'overview' => [
                'total' => $totalThisMonth,
                'travelers' => $totalThisMonth > 0 ? ($tripCountThis / $totalThisMonth) * 100 : 0,
                'agents' => $totalThisMonth > 0 ? ($agentCountThis / $totalThisMonth) * 100 : 0,
                'drivers' => $totalThisMonth > 0 ? ($driverCountThis / $totalThisMonth) * 100 : 0,
                'transport_companies' => $totalThisMonth > 0 ? ($companyCountThis / $totalThisMonth) * 100 : 0,
            ],
            'transport_company_distribution' => [
                'road' => $distribution['road'] ?? 0,
                'train' => $distribution['rail'] ?? 0,
                'air' => $distribution['air'] ?? 0,
                'sea' => $distribution['sea'] ?? 0,
            ],
        ], 'Stats retrieved successfully');
    }

    public function statActivities()
    {

        if (request()->filled('zone')) {
            $states = collect(Zones::tryFrom(request()->input('zone'))?->states());
            $activities = collect();
            $states->map(function ($state) use ($activities) {
                $activities[$state] = $this->actionService->getStateActivityCount($state);
            });

            return $this->success($activities->toArray(), 'Activities retrieved successfully');
        }

        if (request()->filled('state')) {
            return $this->success($this->actionService->getStateActivityCount(request()->input('state'), true)->toArray(), 'Activities retrieved successfully');
        }

        if (request()->filled('user')) {
            return $this->success($this->getStateActivities(request()->input('user')), 'Activities retrieved successfully');
        }

        return $this->success([
            'north_central' => $this->actionService->getZoneActivities(Zones::NORTHCENTRAL->states()),
            'north_east' => $this->actionService->getZoneActivities(Zones::NORTHEAST->states()),
            'north_west' => $this->actionService->getZoneActivities(Zones::NORTHWEST->states()),
            'south_south' => $this->actionService->getZoneActivities(Zones::SOUTHSOUTH->states()),
            'south_east' => $this->actionService->getZoneActivities(Zones::SOUTHEAST->states()),
            'south_west' => $this->actionService->getZoneActivities(Zones::SOUTHWEST->states()),
        ], 'Activities retrieved successfully');
    }

    public function getStateActivities($category = null)
    {
        $categories = ['travellers', 'transport_companies', 'drivers'];
        $states = State::with([
            'cities',
            'departingTrips.bookings',
            'arrivingTrips.bookings',
            'transitCompanies.drivers',
        ])->get();

        $activities = $states->map(function ($state) use ($category, $categories) {
            $departingBookings = $state->departingTrips->flatMap(fn ($trip) => $trip->bookings);
            $arrivingBookings = $state->arrivingTrips->flatMap(fn ($trip) => $trip->bookings);
            $drivers = $state->transitCompanies->flatMap(fn ($company) => $company->drivers);

            $data = [
                'travellers' => $departingBookings->count() + $arrivingBookings->count(),
                'transport_companies' => $state->transitCompanies->count(),
                'drivers' => $drivers->count(),
            ];

            if ($category && in_array($category, $categories)) {

                return [
                    $state->name => $data[$category],
                ];
            }

            return [
                $state->name => $data,
            ];

        });

        return $this->success($activities, 'Activities retrieved successfully');
    }
}
