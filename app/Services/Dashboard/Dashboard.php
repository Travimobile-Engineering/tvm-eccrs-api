<?php

namespace App\Services\Dashboard;

use App\Enums\Zones;
use App\Models\Trip;
use App\Models\Zone;
use App\Models\State;
use App\Models\Incident;
use App\Models\Manifest;
use App\Models\WatchList;
use App\Models\TripBooking;
use App\Traits\HttpResponse;
use Illuminate\Support\Facades\DB;
use App\Models\TripBookingPassenger;

class Dashboard
{
    use HttpResponse;

    public function dashboard()
    {
        $today = now()->startOfDay()->toDateString();      // 'Y-m-d'
        $yesterday = now()->subDay()->startOfDay()->toDateString();

        $sql = [
            "COUNT(CASE WHEN DATE(created_at) = ? THEN 1 END) as today",
            "COUNT(CASE WHEN DATE(created_at) = ? THEN 1 END) as yesterday",
        ];

        $passengersSQL = $manifestsSQL = $watchlistsSQL = $sql;
        $incidentsSQL = [
            "COUNT(CASE WHEN DATE(date) = ? THEN 1 END) as today",
            "COUNT(CASE WHEN DATE(date) = ? THEN 1 END) as yesterday",
        ];
        $passengerSqlBindings = [];
        $manifestSqlBindings = [];
        $watchlistSqlBindings = [];
        $incidentSqlBindings = [];

        for($i=0; $i < 4; $i++){
            $passengerSqlBindings = [$today, $yesterday];
            $manifestSqlBindings = [$today, $yesterday];
            $watchlistSqlBindings = [$today, $yesterday];
            $incidentSqlBindings = [$today, $yesterday];
        }

        $zones = Zones::cases();
        foreach ($zones as $zone) {
            $alias = str_replace('-', '_', $zone->value);

            $zoneRecord = (new Zone)->setConnection('transport')->where('name', $zone->value)->first();
            if (is_null($zoneRecord)) {
                continue;
            }
            $zoneID = $zoneRecord->id;

            // Collect relevant IDs outside SQL to avoid nested subqueries
            $tripIds = Trip::where('zone_id', $zoneID)->pluck('id')->toArray();
            $tripBookingIds = TripBooking::whereIn('trip_id', $tripIds)->pluck('id')->toArray();
            $stateIds = State::where('zone_id', $zoneID)->pluck('id')->toArray();

            $passengersSQL[] = "COUNT(CASE WHEN trip_booking_id IN (" . implode(',', $tripBookingIds ?: [0]) . ") AND DATE(created_at) = ? THEN 1 END) as {$alias}_passengers";
            $passengerSqlBindings[] = $today;
            $manifestsSQL[] = "COUNT(CASE WHEN trip_id IN (" . implode(',', $tripIds ?: [0]) . ") AND DATE(created_at) = ? THEN 1 END) as {$alias}_manifests";
            $manifestSqlBindings[] = $today;
            $watchlistsSQL[] = "COUNT(CASE WHEN state_id IN (" . implode(',', $stateIds ?: [0]) . ") AND DATE(created_at) = ? THEN 1 END) as {$alias}_watchlists";
            $watchlistSqlBindings[] = $today;
            $incidentsSQL[] = "COUNT(CASE WHEN state_id IN (" . implode(',', $stateIds ?: [0]) . ") AND DATE(date) = ? THEN 1 END) as {$alias}_incidents";
            $incidentSqlBindings[] = $today;
        }
        
        $db = [
            'passengers' => TripBookingPassenger::selectRaw(implode(',', $passengersSQL), $passengerSqlBindings)
            ->whereHas('tripBooking.trip', function($query){
                $query->when(request('mode'), fn($q, $m) => $q->where('means', $m));
            })
            ->first(),
            'manifests' => Manifest::selectRaw(implode(',', $manifestsSQL), $manifestSqlBindings)
            ->whereHas('trip', function($query){
                $query->when(request('mode'), fn($q, $m) => $q->where('means', $m));
            })
            ->first(),
            'incidents' => Incident::selectRaw(implode(',', $incidentsSQL), $incidentSqlBindings)->first(),
            'watchlists' => WatchList::selectRaw(implode(',', $watchlistsSQL), $watchlistSqlBindings)->first(),
        ];

        $data = [];

        foreach ($db as $key => $modelData) {
            $zoneData = [];

            foreach ($zones as $zone) {
                $alias = str_replace('-', '_', $zone->value);
                $column = "{$alias}_$key";
                $zoneData[$alias] = $modelData->$column ?? 0;
            }

            $data[$key] = array_merge([
                'today' => $modelData->today ?? 0,
                'percentDiff' => calculatePercentageOf($modelData->today ?? 0, $modelData->yesterday ?? 0),
            ], $zoneData);
        }

        return $this->success([
            ...$data,
            'guests' => null
        ], 'Dashboard data retrieved successfully');
    }

}
