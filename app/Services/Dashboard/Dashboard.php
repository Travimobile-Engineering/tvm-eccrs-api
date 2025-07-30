<?php

namespace App\Services\Dashboard;

use App\Enums\Zones;
use App\Models\Zone;
use App\Models\Incident;
use App\Models\Manifest;
use App\Models\WatchList;
use App\Traits\HttpResponse;
use App\Models\TripBookingPassenger;

class Dashboard
{
    use HttpResponse;
    
    public function dashboard()
    {
        $now = now();
        $today = $now->copy()->today();
        $yesterday = $now->copy()->subDay();
        
        $sql = [
            "COUNT(CASE WHEN DATE(created_at) = '$today' THEN 1 END) as today",
            "COUNT(CASE WHEN DATE(created_at) = '$yesterday' THEN 1 END) as yesterday",
        ];

        $passengersSQL = [...$sql];
        $manifestsSQL = [...$sql];
        $watchlistsSQL = [...$sql];
        
        $incidentsSQL = [
            "COUNT(CASE WHEN DATE(date) = '$today' THEN 1 END) as today",
            "COUNT(CASE WHEN DATE(date) = '$yesterday' THEN 1 END) as yesterday",
        ];

        $zones = Zones::cases();
        foreach($zones as $zone){
            $alias = str_replace('-', '_', $zone->value);
            $zoneID = (new Zone)->setConnection('transport')
            ->where('name', $zone->value)
            ->first()?->id;

            $passengersSQL[] = "COUNT(
                CASE 
                    WHEN trip_booking_id IN (
                        SELECT id FROM trip_bookings WHERE trip_id IN (
                            SELECT id FROM trips WHERE zone_id = $zoneID
                        )
                    ) THEN 1
                END
            ) as {$alias}_passengers";

            $manifestsSQL[] = "COUNT(
                CASE WHEN trip_id IN (
                    SELECT id FROM trips WHERE zone_id = $zoneID 
                ) THEN 1
                END
            ) as {$alias}_manifests";

            $watchlistsSQL[] = "COUNT(
                CASE WHEN state_id IN (
                    SELECT id FROM states WHERE zone_id = $zoneID
                ) THEN 1
                END
            ) as {$alias}_watchlists";

            $incidentsSQL[] = "COUNT(
                CASE WHEN state_id IN (
                    SELECT id FROM states WHERE zone_id = $zoneID
                ) THEN 1
                END
            ) as {$alias}_incidents";
        }

        $db = [
            'passengers' => TripBookingPassenger::selectRaw(implode(',', $passengersSQL))->first(),
            'manifests' => Manifest::selectRaw(implode(',', $manifestsSQL))->first(),
            'incidents' => Incident::selectRaw(implode(',', $incidentsSQL))->first(),
            'watchlists' => WatchList::selectRaw(implode(',', $watchlistsSQL))->first(),
        ];
        
        $data = [
            'passengers' => null,
            'manifests' => null,
            'incidents' => null,
            'watchlists' => null,
        ];

        foreach($data as $key => $value){
            $data[$key] = [
                'today' => $db[$key]->today,
                'percentDiff' => calculatePercentageOf($db[$key]->today, $db[$key]->yesterday),
            ];
            foreach($zones as $zone){
                $alias = str_replace('-', '_', $zone->value);
                $data[$key][$alias] = $db[$key]->{$alias . "_$key"};
            }
                
        }
        return $this->success([
            ...$data,
            'guests' => null
        ], 'Dashboard data retrieved successfully');
    }
}
