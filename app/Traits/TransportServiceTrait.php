<?php

namespace App\Traits;

use App\Models\Trip;
use Illuminate\Support\Collection;

trait TransportServiceTrait
{
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

    protected function getTotalBookings(Collection $bookings): int
    {
        return $bookings->count() + $bookings->sum(function($booking){
            return $booking->travellingWith->count();
        });
    }
}
