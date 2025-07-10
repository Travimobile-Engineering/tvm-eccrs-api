<?php

namespace App\Traits;

trait ManifestFilter
{
    public function scopeFilterByUserZone($query, $user)
    {
        if ($user->role && $user->role !== 'super_admin') {
            $query->whereHas('trip', function ($q) use ($user) {
                $q->where('zone_id', $user->zone_id);
            });
        }
    }

    public function scopeFilterByReport($query, $filters)
    {
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;
        $zone = $filters['zone'] ?? null;
        $state = $filters['state'] ?? null;
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;

        return $query
            ->when($startDate && $endDate, fn ($q) => $q->whereHas('trip', fn ($trip) => $trip->whereBetween('departure_date', [$startDate, $endDate])
            )
            )
            ->when($zone && $zone !== 'all', fn ($q) => $q->whereTripZone($zone)
            )
            ->when($state && $state !== 'all', fn ($q) => $q->whereTripState($state)
            )
            ->when($from, fn ($q) => $q->whereTripFrom($from)
            )
            ->when($to, fn ($q) => $q->whereTripTo($to)
            );
    }

    public function scopeWhereTripZone($query, $zone)
    {
        return $query->whereHas('trip', fn ($q) => $q->where('zone_id', $zone)
        );
    }

    public function scopeWhereTripState($query, $state)
    {
        return $query->whereHas('trip', fn ($q) => $q->whereHas('destinationCity', fn ($region) => $region->where('state_id', $state)
        )
        );
    }

    public function scopeWhereTripFrom($query, $from)
    {
        return $query->whereHas('trip', fn ($q) => $q->where('from', 'like', "%$from%")
        );
    }

    public function scopeWhereTripTo($query, $to)
    {
        return $query->whereHas('trip', fn ($q) => $q->where('to', 'like', "%$to%")
        );
    }
}
