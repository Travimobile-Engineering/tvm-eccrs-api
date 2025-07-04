<?php

namespace App\Services;

use App\Models\WatchList;
use App\Traits\HttpResponse;
use App\Http\Resources\WatchlistResource;

class WatchlistService
{
    use HttpResponse;

    public function getWatchlistRecords()
    {
        $records = WatchList::when(request('search'), fn($q, $search) => $q->where('full_name', 'like', "%$search%"))
        ->paginate(15);
        return $this->success(WatchlistResource::collection($records), 'Watchlist records fetched successfully');
    }

    public function getWatchlistDetail($id)
    {
        $record = WatchList::with([
            'userByNin.tripBookings.trip' => fn($q) => $q->with('transitCompany', 'departureState', 'departureCity', 'destinationState', 'destinationCity'),
            'userByPhone.tripBookings.trip'  => fn($q) => $q->with('transitCompany', 'departureState', 'departureCity', 'destinationState', 'destinationCity'), 
            'userByEmail.tripBookings.trip'  => fn($q) => $q->with('transitCompany', 'departureState', 'departureCity', 'destinationState', 'destinationCity')
        ])->findOrFail($id);
        return $this->success($record->toResource(), 'Watchlist record fetched successfully');
    }

    public function watchlistStats()
    {
        $watchlistsQuery = WatchList::when(request('status'), fn($q, $status) => $q->where('status', $status))
            ->when(request('month'), function($q, $month) {
                $curMonth = now()->monthOfYear();
                return $q->whereBetween('created_at', [now()->subMonths($curMonth - $month)->startOfMonth(), now()->subMonths($curMonth - $month)->endOfMonth()]);
            });
    
        $totalCount = (clone $watchlistsQuery)->count();
        $apprehendedCount = (clone $watchlistsQuery)->where('status', 'in-custody')->count();
    
        $previousMonthStart = now()->subMonth()->startOfMonth();
        $previousMonthEnd = now()->subMonth()->endOfMonth();
    
        $previousMonthCount = (clone $watchlistsQuery)
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
            ->count();
    
        $previousMonthApprehendedCount = (clone $watchlistsQuery)
            ->where('status', 'in-custody')
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
            ->count();
    
        $resource = WatchlistResource::collection($watchlistsQuery->paginate(25))->additional(
            [
                'entries' => [
                    'total' => $totalCount,
                    'percentDiff' => calculatePercentageDifference(
                        $totalCount, 
                        $previousMonthCount
                    ),
                ],
                'apprehended' => [
                    'total' => $apprehendedCount,
                    'percentDiff' => calculatePercentageDifference(
                        $apprehendedCount, 
                        $previousMonthApprehendedCount
                    ),
                ],
            ]);
        return $this->success($resource, 'Watchlist statistics fetched successfully');
    }
}
