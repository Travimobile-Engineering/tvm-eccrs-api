<?php

namespace App\Services;

use App\Enums\Enums\WatchlistStatus;
use App\Http\Resources\WatchlistResource;
use App\Models\User;
use App\Models\WatchList;
use App\Traits\HttpResponse;

class WatchlistService
{
    use HttpResponse;

    public function getWatchlistRecords()
    {
        $records = WatchList::when(request('search'), function ($q, $search) {
            return $q->where('full_name', 'like', "%$search%")
                ->orWhere('nin', 'like', "%$search%")
                ->orWhere('phone', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%");
        })
            ->paginate(15);

        return $this->success(WatchlistResource::collection($records), 'Watchlist records fetched successfully');
    }

    public function getWatchlistDetail($id)
    {
        $record = WatchList::findOrFail($id);
        $user = User::fromWatchlist($record)->first();
        $record->setRelation('user', $user ?? null);

        return $this->success($record->toResource(), 'Watchlist record fetched successfully');
    }

    public function watchlistStats()
    {
        $watchlistsQuery = WatchList::when(request('status'), fn ($q, $status) => $q->whereStatus(WatchlistStatus::tryFrom($status)))
            ->when(request('month'), function ($q, $month) {
                $curMonth = now()->monthOfYear();

                return $q->whereBetween('created_at', [now()->subMonths($curMonth - $month)->startOfMonth(), now()->subMonths($curMonth - $month)->endOfMonth()]);
            });

        $totalCount = (clone $watchlistsQuery)->count();
        $apprehendedCount = (clone $watchlistsQuery)->whereStatus(WatchlistStatus::IN_CUSTODY->value)->count();

        $previousMonthStart = now()->subMonth()->startOfMonth();
        $previousMonthEnd = now()->subMonth()->endOfMonth();

        $previousMonthCount = (clone $watchlistsQuery)
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
            ->count();

        $previousMonthApprehendedCount = (clone $watchlistsQuery)
            ->whereStatus(WatchlistStatus::IN_CUSTODY->value)
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
            ->count();

        $resource = WatchlistResource::collection($watchlistsQuery->paginate(25))->additional(
            [
                'entries' => [
                    'total' => $totalCount,
                    'percentDiff' => calculatePercentageOf(
                        $totalCount, 
                        $previousMonthCount
                    ),
                ],
                'apprehended' => [
                    'total' => $apprehendedCount,
                    'percentDiff' => calculatePercentageOf(
                        $apprehendedCount, 
                        $previousMonthApprehendedCount
                    ),
                ],
            ]);

        return $this->success($resource, 'Watchlist statistics fetched successfully');
    }
}
