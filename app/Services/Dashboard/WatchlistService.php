<?php

namespace App\Services\Dashboard;

use App\Enums\Zones;
use App\Http\Resources\WatchlistResource;
use App\Models\User;
use App\Models\WatchList;
use App\Traits\HttpResponse;

class WatchlistService
{
    use HttpResponse;

    public function overview()
    {
        $now = now();
        $prevMonthStart = $now->copy()->subMonth()->startOfMonth();
        $prevMonthEnd = $now->copy()->subMonth()->endOfMonth();
        $zones = Zones::cases();

        $sql = ['
            COUNT(*) as total,
            COUNT(CASE WHEN created_at BETWEEN ? AND ? THEN 1 END) as lastMonthTotal
        '];

        foreach ($zones as $zone) {
            $alias = str_replace('-', '_', $zone->value).'_count';
            $states = implode("','",
                collect($zone->states())
                    ->map(fn ($state) => preg_replace('/[^a-zA-Z0-9\s]/', '', $state))
                    ->toArray()
            );
            $sql[] = "COUNT(CASE WHEN alert_location IN ('$states') THEN 1 END) as $alias";

            foreach ($zone->states() as $state) {
                $state = preg_replace('/[^a-zA-Z0-9\s]/', '', $state);
                $alias = preg_replace('/-/', '_', $zone->value).'_'.str_replace(' ', '_', $state).'_count';
                $sql[] = "COUNT(CASE WHEN alert_location = '$state' THEN 1 END) as $alias";
            }
        }

        $counts = WatchList::selectRaw(implode(',', $sql),
            [$prevMonthStart, $prevMonthEnd])->first();

        $data = [
            'total' => $counts->total,
            'percentDiff' => calculatePercentageOf($counts->lastMonthTotal, $counts->total),
        ];

        foreach ($zones as $zone) {
            $states = $zone->states();
            $zoneKey = str_replace('-', '_', $zone->value);

            $statesData = [];
            foreach ($states as $state) {
                $stateKey = str_replace(' ', '_', $state);
                $statesData[$stateKey] = $counts[$zoneKey.'_'.$stateKey.'_count'] ?? 0;
            }
            $data[$zoneKey] = [
                'total' => $counts[$zoneKey.'_count'],
                ...$statesData,
            ];
        }

        return $this->success($data, 'Watchlist data retrieved successfully');
    }

    public function list()
    {

        $payload = (object) [
            'zone' => request('zone'),
            'state' => request('state'),
        ];

        $states = [];
        if (! empty($payload->zone) && empty($payload->state)) {
            $zone = Zones::tryFrom(request('zone'));
            if ($zone) {
                $states = $zone->states();
            } else {
                return $this->error(null, 'Invalid zone name');
            }
        }

        $watchlist = Watchlist::when(! empty($states), function ($query, $states) {
            $query->whereHas('state', function($query) use ($states) {
                $query->whereIn('name', $states);
            });
        })
        ->when(request('state'), function ($query, $state) {
            $query->whereHas('state', function($query) use ($state) {
                $query->where('name', $state);
            });
        })
        ->paginate(25);

        return $this->withPagination(WatchlistResource::collection($watchlist), 'Watchlist records retrieved successfully');

    }

    public function getRecord($id)
    {
        $record = User::fromWatchlist(Watchlist::findOrFail($id))->first();
        if(! $record) {
            return $this->error(null, 'Watchlist record not found', 404);
        }
        return $this->success((new WatchlistResource($record)), 'Watchlist record retrieved successfully');
    }
}
