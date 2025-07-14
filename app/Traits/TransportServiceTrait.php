<?php

namespace App\Traits;

use App\Models\Trip;
use App\Models\TripBooking;
use App\Models\TransitCompany;
use Illuminate\Support\Collection;

trait TransportServiceTrait
{
    protected function getInboundPassengers(array $states, $from = null, $to = null)
    {
        $trips = Trip::whereHas('destinationState', fn ($q) => $q->whereIn('states.name', $states))
            ->between($from ?? now()->startOfMonth(), $to ?? now())
            ->get();

        return (object) [
            'total' => $trips->count(),
            'road' => $trips->filter(fn ($trip) => $trip->means === 'road')->count(),
            'air' => null,
            'sea' => null,
            'rail' => null,
        ];
    }

    protected function getOutboundPassengers(array $states, $from = null, $to = null)
    {
        $trips = Trip::whereHas('departureState', fn ($q) => $q->whereIn('states.name', $states))
            ->between($from ?? now()->startOfMonth(), $to ?? now())
            ->get();

        return (object) [
            'total' => $trips->count(),
            'road' => $trips->filter(fn ($trip) => $trip->means === 'road')->count(),
            'air' => null,
            'sea' => null,
            'rail' => null,
        ];
    }

    protected function setInboundOutboundData(array $states)
    {
        if (gettype($states) !== 'array') {
            $states = [$states];
        }

        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();
        $inboundData = $this->getInboundPassengers($states);
        $outboundData = $this->getOutboundPassengers($states);
        $lastMonthInboundPassengers = $this->getInboundPassengers($states, $lastMonthStart, $lastMonthEnd);
        $lastMonthOutboundPassengers = $this->getOutboundPassengers($states, $lastMonthStart, $lastMonthEnd);

        return (object) [
            'inboundData' => $inboundData,
            'outboundData' => $outboundData,
            'lastMonthInboundPassengersCount' => $lastMonthInboundPassengers->total,
            'lastMonthOutboundPassengersCount' => $lastMonthOutboundPassengers->total,
            'inboundPercentageDiff' => calculatePercentageOf($lastMonthInboundPassengers->total, $inboundData->total),
            'outboundPercentageDiff' => calculatePercentageOf($lastMonthOutboundPassengers->total, $outboundData->total),
        ];
    }

    protected function getTotalBookings(Collection $bookings): int
    {
        return $bookings->flatMap->travellingWith->count();
    }

    protected function getTotalConfirmedBookings(Collection $bookings): int
    {
        return $bookings->flatMap->travellingWith->filter->on_seat->count();
    }

    protected function getTotalUnconfirmedBookings(Collection $bookings): int
    {
        return $bookings->flatMap->travellingWith->reject->on_seat->count();
    }

    protected function getTotalCancelledBookings(Collection $bookings): int
    {
        return $bookings->flatMap->travellingWith->filter(fn ($b) => $b->status === 0)->count();
    }

    protected function sortColumn($sort){
        return explode(',', $sort)[0] ?? 'created_at';
    }

    protected function sortOrder($sort){
        return explode(',', $sort)[1] ?? 'desc';
    }

    public function setZoneId($zoneId)
    {
        if(! empty(request()->header('zone_id'))) {
            Trip::setZoneId($zoneId);
            TripBooking::setZoneId($zoneId);
            TransitCompany::setZoneId($zoneId);
        }
    }
}
