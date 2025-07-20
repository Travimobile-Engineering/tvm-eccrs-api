<?php

namespace App\Services\Dashboard;

use Str;
use App\Enums\Zones;
use App\Models\User;
use App\Models\WatchList;
use App\Traits\HttpResponse;
use App\Http\Resources\WatchlistResource;

class WatchlistService
{
    use HttpResponse;

    public function overview()
    {
        $now = now();
        $prevMonthStart = $now->subMonth()->startOfMonth();
        $prevMonthEnd = $now->subMonth()->endOfMonth();
        $zones = Zones::cases();
        
        $sql = '
            COUNT(*) as total,
            COUNT(CASE WHEN created_at BETWEEN ? AND ? THEN 1 END) as lastMonthTotal,
        ';
        foreach($zones as $zone){
            $alias = str_replace('-', '_', $zone->value) . '_count';
            $states = implode("','", $zone->states());
            $sql .= "COUNT(CASE WHEN alert_location IN ('$states') THEN 1 END) as $alias,";
            
            foreach($zone->states() as $state){
                $alias = preg_replace('/-/', '_', $zone->value) . '_' . str_replace(' ', '_', $state) .'_count';
                $sql .= "COUNT(CASE WHEN alert_location = '$state' THEN 1 END) as $alias,";
            }
        }
        
        $counts = WatchList::selectRaw(rtrim($sql, ','),
        [$prevMonthStart, $prevMonthEnd])->first();

        $data = [
            'total' => $counts->total,
            'percentDiff' => calculatePercentageOf($counts->lastMonthTotal, $counts->total),
        ];
        
        foreach($zones as $zone){
            $states = $zone->states();
            $zone = str_replace('-', '_', $zone->value);
            
            $statesData = [];
            foreach($states as $state){
                $state = str_replace(' ', '_', $state);
                $statesData[$state] = $counts[$zone . '_' . $state . '_count'];
            }
            $data[$zone] = [
                'total' => $counts[$zone .'_count'],
                ...$statesData,
            ];
        }

        return $this->success($data, 'Watchlist data retrieved successfully');
    }

    public function list(){

        if(request()->filled('zone') && empty(request('state'))){
            $zone = Zones::tryFrom(request('zone'));
            if($zone){
                $states = $zone->states();
            }
            else{
                return $this->error(null, 'Invalid zone name');
            }
        }
        
        $watchlist = Watchlist::when(! empty($states), function($query, $states){
                $query->whereIn('alert_location', $states);
        })
        ->when(request('state'), function($query, $state){
            $query->where('alert_location', $state);
        })
        ->paginate(25);

        return $this->withPagination(WatchlistResource::collection($watchlist), 'Watchlist records retrieved successfully');

    }

    public function getRecord($id){
        $record = User::fromWatchlist(Watchlist::findOrFail($id))->first();
        return $this->success((new WatchlistResource($record)), 'Watchlist record retrieved successfully');
    }
}
