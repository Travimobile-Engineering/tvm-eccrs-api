<?php

namespace App\Services;

use App\Enums\Zones;
use App\Models\Trip;
use App\Models\User;
use App\Models\State;

use App\Models\TripBooking;
use App\Traits\HttpResponse;
use App\Models\TransitCompany;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Schema;
use function App\Helpers\calculatePercentageDifference;

class UserService
{
    use HttpResponse;

    public function getTravellers(){
        $travellers = User::whereHas('tripBookings')->paginate(25);
        return UserResource::collection($travellers);
    }

    public function getUserDetail($id){
        $user = User::with([
            'vehicle.brand',
            'watchlists',
            'trips' => fn($q) => $q->with(['manifest', 'booking', 'departureCity.state', 'destinationCity.state']),
            'tripBookings.trip' => fn($query) => $query->with(['transitCompany', 'departureCity.state', 'destinationCity.state']),
        ])->findOrFail($id);
        return $this->success(new UserResource($user));
    }

    public function getAgents(){
        $agents = User::where('agent_id', '!=', null)
            ->where('agent_id', '!=', '')
            ->paginate(25);
        return UserResource::collection($agents);
    }

    public function  getDrivers(){
        $drivers = User::with(['documents', 'union',])->whereHas('vehicle')->paginate(25);
        return UserResource::collection($drivers);
    }

    public function stats(){
        $firstDayOfLastMonth = now()->parse('first day of last month 00:00:00');
        $lastDayOfLastMonth = now()->parse('last day of last month 23:59:59');
        $firstDayOfThisMonth = now()->parse('first day of this month 00:00:00');
        $today = now()->parse('today 00:00:00');

        $lastMonthTripBooking = TripBooking::whereBetween('created_at', [$firstDayOfLastMonth, $lastDayOfLastMonth])->count();
        $thisMonthTripBooking = TripBooking::whereBetween('created_at', [$firstDayOfThisMonth, $today])->count();
        $lastMonthThisMonthTripBooking = calculatePercentageDifference($lastMonthTripBooking, $thisMonthTripBooking);

        $lastMonthAgentSignup = User::where('agent_id', '!=', null)->whereBetween('created_at', [$firstDayOfLastMonth, $lastDayOfLastMonth])->count();
        $thisMonthAgentSignup = User::where('agent_id', '!=', null)->whereBetween('created_at', [$firstDayOfThisMonth, $today])->count();
        $lastMonthThisMonthAgentSignup = calculatePercentageDifference($lastMonthAgentSignup, $thisMonthAgentSignup);

        $lastMonthDriverSignup = User::whereHas('vehicle')->whereBetween('created_at', [$firstDayOfLastMonth, $lastDayOfLastMonth])->count();
        $thisMonthDriverSignup = User::whereHas('vehicle')->whereBetween('created_at', [$firstDayOfThisMonth, $today])->count();
        $lastMonthThisMonthDriverSignup = calculatePercentageDifference($lastMonthDriverSignup, $thisMonthDriverSignup);

        $lastMonthTransportCompanySignup = TransitCompany::whereBetween('created_at', [$firstDayOfLastMonth, $lastDayOfLastMonth])->count();
        $thisMonthTransportCompanySignup = TransitCompany::whereBetween('created_at', [$firstDayOfThisMonth, $today])->count();
        $lastMonthThisMonthTransportCompanySignup = calculatePercentageDifference($lastMonthTransportCompanySignup, $thisMonthTransportCompanySignup);

        $thisMonthTotal = $thisMonthTripBooking + $thisMonthAgentSignup + $thisMonthDriverSignup + $thisMonthTransportCompanySignup;
        
        return $this->success([
            "travelers" => [
                "total" => $thisMonthTripBooking,
                "percentageDiff" => $lastMonthThisMonthTripBooking,
            ],
            "agents" => [
                "total" => $thisMonthAgentSignup,
                "percentageDiff" => $lastMonthThisMonthAgentSignup,
            ],
            "drivers" => [
                "total" => $thisMonthDriverSignup,
                "percentageDiff" => $lastMonthThisMonthDriverSignup,
            ],
            "transport_companies" => [
                "total" => $thisMonthTransportCompanySignup,
                "percentageDiff" => $lastMonthThisMonthTransportCompanySignup,
            ],
            "overview" => [
                'total' => $thisMonthTotal,
                'travelers' => $thisMonthTotal > 0 ? ($thisMonthTripBooking / $thisMonthTotal) * 100 : 0,
                'agents' => $thisMonthTotal > 0 ? ($thisMonthAgentSignup / $thisMonthTotal) * 100 : 0,
                'drivers' => $thisMonthTotal > 0 ? ($thisMonthDriverSignup / $thisMonthTotal) * 100 : 0,
                'transport_companies' => $thisMonthTotal > 0 ? ($thisMonthTransportCompanySignup / $thisMonthTotal) * 100 : 0,
            ],
            "transport_company_distribution" => [
                "road" => TransitCompany::where('type', 'road')->count(),
                "train" => TransitCompany::where('type', 'rail')->count(),
                "air" => TransitCompany::where('type', 'air')->count(),
                "sea" => TransitCompany::where('type', 'sea')->count()
            ],
            "activities" => [
                'north_central' => $this->getZoneActivities(Zones::NORTHCENTRAL),
                'north_east' => $this->getZoneActivities(Zones::NORTHEAST),
                'north_west' => $this->getZoneActivities(Zones::NORTHWEST),
                'south_south' => $this->getZoneActivities(Zones::SOUTHSOUTH),
                'south_east' => $this->getZoneActivities(Zones::SOUTHEAST),
                'south_west' => $this->getZoneActivities(Zones::SOUTHWEST),
            ]
        ]);
    }

    public function getStateActivities(){
        $states = State::with('cities')->get();
        $states = $states->map(function($state){
            return [
                "state" => $state->name,
                "state_id" => $state->id,
                "cities" => $state->cities->map(function($city){
                    return $city->id;
                })
            ];
        });

        $activities = $states->map(function($state){
            $trips = Trip::with('bookings')->whereIn('destination', $state['cities'])->orWhereIn('departure', $state['cities'])->get();
            $bookings = $trips->map(fn($trip) => $trip->bookings);

            $transitCompanies = TransitCompany::with('drivers')->where('state', $state['state'])->get();
            $drivers = $transitCompanies->map(function($transitCompany){
                    return $transitCompany->drivers->count();
                })->toArray();

            return [
                $state['state'] => [
                    'travelers' => $bookings->count(),
                    'drivers' => array_sum($drivers),
                    'transport_companies' => $transitCompanies->count(),
                ] 
            ];
        });

        return $this->success($activities);
    }

    private function getZoneActivities(array $zone){
        $cities = State::getZonecities($zone);
        $trips = Trip::with('bookings')->whereIn('departure', $cities)->orWhereIn('destination', $cities)->get();
        $bookings = $trips->map(fn($trip) => $trip->bookings);
        return $bookings->count();
    }
}
