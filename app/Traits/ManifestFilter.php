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
        return $query
            ->when($filters['start_date'] && $filters['end_date'], fn ($q) => $q->whereHas('trip', fn ($trip) => $trip->whereBetween('departure_date', [$filters['start_date'], $filters['end_date']])
            )
            )
            ->when($filters['zone'] && $filters['zone'] !== 'all', fn ($q) => $q->whereTripZone($filters['zone'])
            )
            ->when($filters['state'] && $filters['state'] !== 'all', fn ($q) => $q->whereTripState($filters['state'])
            )
            ->when($filters['from'], fn ($q) => $q->whereTripFrom($filters['from'])
            )
            ->when($filters['to'], fn ($q) => $q->whereTripTo($filters['to'])
            );
    }

    public function scopeWhereTripZone($query, $zone)
    {
        return $query->whereHas('trip', fn ($q) => $q->where('zone_id', $zone)
        );
    }

    public function scopeWhereTripState($query, $state)
    {
        return $query->whereHas('trip', fn ($q) => $q->whereHas('destinationRegion', fn ($region) => $region->where('state_id', $state)
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
