<?php

namespace App\Traits;

trait TripFilter
{
    public function scopeFilterByReport($query, $filters)
    {
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;
        $zone = $filters['zone'] ?? null;
        $state = $filters['state'] ?? null;
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;

        return $query
            ->when($state && $state !== 'all', fn ($q) => $q->whereTripState($state))
            ->when($zone && $zone !== 'all', fn ($q) => $q->whereTripZone($zone))
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('departure_date', [$startDate, $endDate]))
            ->when($from && $to, fn ($q) => $q->where('from', 'like', "%$from%")
                ->where('to', 'like', "%$to%")
            );
    }

    public function scopeBetween($query, $from, $to)
    {
        $query->whereBetween('created_at', [$from, $to]);
    }

    public function scopeFilterByUserZone($query, $user)
    {
        if ($user->role && $user->role !== 'super_admin') {
            $query->whereHas('trip', function ($q) use ($user) {
                $q->where('zone_id', $user->zone_id);
            });
        }
    }

    public function scopeWhereTripState($query, $stateId)
    {
        return $query->whereHas('destinationCity', function ($q) use ($stateId) {
            $q->whereHas('state', function ($q2) use ($stateId) {
                $q2->where('id', $stateId);
            });
        });
    }

    public function scopeWhereTripZone($query, $zoneId)
    {
        return $query->where('zone_id', $zoneId);
    }
}
