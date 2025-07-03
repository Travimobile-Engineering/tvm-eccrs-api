<?php

namespace App\Services;

use App\Models\WatchList;
use App\Traits\HttpResponse;

class WatchlistService
{
    use HttpResponse;

    public function getWatchlistRecords()
    {
        $records = WatchList::when(request('search'), fn($q, $search) => $q->where('full_name', 'like', "%$search%"))
        ->paginate(15);
        return $this->withPagination($records->toResourceCollection(), 'Watchlist records fetched successfully');
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
}
