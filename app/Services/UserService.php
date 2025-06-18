<?php

namespace App\Services;

use App\Enums\UserType;
use App\Enums\Zones;
use App\Models\State;
use App\Models\TransitCompany;
use App\Models\TripBooking;
use App\Models\User;
use App\Traits\HttpResponse;

class UserService
{
    use HttpResponse;

    public function getTravellers()
    {
        $travellers = User::whereHas('tripBookings');
        if(request()->input()){
            $inputs = request()->input();
            if(array_key_exists('name', $inputs)){
                $travellers->where('first_name', 'like', '%'.$inputs['name'].'%')
                    ->orWhere('last_name', 'like', '%'.$inputs['name'].'%');
            }

            if(array_key_exists('nin', $inputs)){
                $travellers->where('nin', $inputs['nin']);
            }

            if(array_key_exists('id', $inputs)){
                $travellers->where('id', $inputs['id']);
            }
        }

        return $this->withPagination($travellers->paginate(25)->toResourceCollection(), 'Travellers retrieved successfully');
    }

    public function getUserDetail($id)
    {
        $user = User::with([
            'vehicle.brand',
            'watchlists',
            'trips' => fn ($q) => $q->with(['manifest', 'booking', 'departureCity.state', 'destinationCity.state']),
            'tripBookings.trip' => fn ($query) => $query->with(['transitCompany', 'departureCity.state', 'destinationCity.state']),
        ])->findOrFail($id);

        return $this->success($user->toResource(), 'User retrieved successfully');
    }

    public function getAgents()
    {
        // $agents = User::isAgent()->paginate(25);
        $agents = User::isAgent();
        if(request()->input()){
            $inputs = request()->input();
            if(array_key_exists('name', $inputs)){
                $agents->where('first_name', 'like', '%'.$inputs['name'] .'%')
                ->orWhere('last_name', 'like', '%'.$inputs['name'].'%');
            }

            if(array_key_exists('id', $inputs)){
                $agents->where('id', 'like', '%'.$inputs['id'].'%');
            }
        }
        return $this->withPagination($agents->paginate(25)->toResourceCollection(), 'Agents retrieved successfully');
    }

    public function getDrivers()
    {
        $drivers = User::with(['documents', 'union'])
            ->where('user_category', UserType::DRIVER->value)
            ->whereHas('vehicle');
            if(request()->input()){
                $inputs = request()->input();
                if(array_key_exists('name', $inputs)){
                    $drivers->where('first_name', 'like', '%'.$inputs['name'].'%')->orWhere('last_name', 'like', '%'.$inputs['name'].'%');
                }

                if(array_key_exists('id', $inputs)){
                    $drivers->where('id', 'like', '%'.$inputs['id'].'%');
                }

                if(array_key_exists('nin', $inputs)){
                    $drivers->where('nin', 'like', '%'.$inputs['nin'].'%');
                }
            }
            return $this->withPagination($drivers->paginate(25)->toResourceCollection(), 'Drivers retrieved successfully');
           
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
            'activities' => [
                'north_central' => $this->getZoneActivities(Zones::NORTHCENTRAL->states()),
                'north_east' => $this->getZoneActivities(Zones::NORTHEAST->states()),
                'north_west' => $this->getZoneActivities(Zones::NORTHWEST->states()),
                'south_south' => $this->getZoneActivities(Zones::SOUTHSOUTH->states()),
                'south_east' => $this->getZoneActivities(Zones::SOUTHEAST->states()),
                'south_west' => $this->getZoneActivities(Zones::SOUTHWEST->states()),
            ],
        ], 'Stats retrieved successfully');
    }

    public function getStateActivities()
    {
        $states = State::with([
            'cities',
            'departingTrips.bookings',
            'arrivingTrips.bookings',
            'transitCompanies.drivers',
        ])->get();

        $activities = $states->map(function ($state) {
            $departingBookings = $state->departingTrips->flatMap(fn ($trip) => $trip->bookings);
            $arrivingBookings = $state->arrivingTrips->flatMap(fn ($trip) => $trip->bookings);
            $drivers = $state->transitCompanies->flatMap(fn ($company) => $company->drivers);

            return [
                $state->name => [
                    'travelers' => $departingBookings->count() + $arrivingBookings->count(),
                    'transport_companies' => $state->transitCompanies->count(),
                    'drivers' => $drivers->count(),
                ],
            ];
        });

        return $this->success($activities, 'Activities retrieved successfully');
    }

    private function getZoneActivities(array $zone): int
    {
        $cities = State::getZoneCities($zone);

        return TripBooking::whereHas('trip', function ($query) use ($cities) {
            $query->whereIn('departure', $cities)
                ->orWhereIn('destination', $cities);
        })->count();
    }
}