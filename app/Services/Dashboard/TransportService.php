<?php

namespace App\Services\Dashboard;

use App\Enums\Zones;
use App\Models\TripBooking;
use App\Traits\HttpResponse;

class TransportService
{
    use HttpResponse;

    public function getTransportData()
    {
        $prevMonthStart =  now()->subMonth()->startOfMonth();
        $prevMonthEnd = now()->subMonth()->endOfMonth();
        $thisMonthStart = now()->startofMonth();
        $thisMonthEnd = now()->endOfMonth();
        
        $sql = [
            "COUNT(*) as total",
            "COUNT(CASE WHEN created_at BETWEEN ? AND ? THEN 1 END) as this_month_total",
            "COUNT(CASE WHEN created_at BETWEEN ? AND ? THEN 1 END) as prev_month_total",
            "COUNT(CASE WHEN 
                trip_id IN (
                    SELECT id FROM trips WHERE means = 'road'
                )
                THEN 1 END
            ) as total_road_count"
        ];
        $bindings = [$thisMonthStart, $thisMonthEnd, $prevMonthStart, $prevMonthEnd];
        
        $zones = Zones::cases();
        foreach($zones as $zone){
            $zone_alias = str_replace('-', '_', $zone->value);
            $states_string = implode("','", 
                collect($zone->states())
                    ->map(fn($state) => preg_replace('/[^a-zA-Z0-9\s]/', '', $state))
                    ->toArray()
                );
                
                $sql[] = "
                    COUNT(CASE WHEN 
                        trip_id IN (
                            SELECT id FROM trips WHERE departure IN (
                                SELECT id FROM states WHERE name in ('$states_string')
                            )
                        ) THEN 1 END
                    ) as {$zone_alias}_outbound_count";
                
                $sql[] = "
                    COUNT(CASE WHEN 
                        trip_id IN (
                            SELECT id FROM trips WHERE destination IN (
                                SELECT id FROM states WHERE name in ('$states_string')
                            )
                        ) THEN 1 END
                    ) as {$zone_alias}_inbound_count";

            foreach($zone->states() as $state){
                $state = preg_replace('/[^a-zA-Z0-9\s]/', '', $state);
                $state_alias = $zone_alias . '_' . str_replace(' ', '_', $state);

                $sql[] = "
                    COUNT(CASE 
                        WHEN trip_id IN (
                            SELECT id FROM trips 
                            WHERE destination = (
                                SELECT id FROM states WHERE name = '$state'
                            )
                        ) THEN 1 END
                    ) as {$state_alias}_inbound_count";

                $sql[] = "
                    COUNT(CASE 
                        WHEN trip_id IN (
                            SELECT id FROM trips 
                            WHERE departure = (
                                SELECT id FROM states WHERE name = '$state'
                            )
                        ) THEN 1 END
                    ) as {$state_alias}_outbound_count";
            }
        }

        $bookings = TripBooking::selectRaw(implode(',', $sql), $bindings)
            ->when(request('mode'), fn($q, $mode) => $q->whereHas('trip', fn($q) => $q->where('means', $mode)))
            ->first();
        
        $data = [
            'total' => $bookings->total,
            'percentDiff' => $bookings->percent_diff = calculatePercentageOf($bookings->this_month_total, $bookings->prev_month_total),
        ];

        foreach($zones as $zone){
            $zone_alias = str_replace('-', '_', $zone->value);
            $data[$zone_alias] = [
                'total_inbound' => $bookings[$zone_alias."_inbound_count"],
                'total_outbound' => $bookings[$zone_alias."_outbound_count"],
            ];
            $states = $zone->states();
            foreach($states as $state){
                $state_alias = str_replace(' ', '_', $state);
                $data[$zone_alias][$state] = [
                    'total_inbound' => $bookings[$zone_alias."_$state_alias"."_inbound_count"],
                    'total_outbound' => $bookings[$zone_alias."_$state_alias"."_outbound_count"],
                ];
            }
        }
        
        $data['mode'] = [
            'road' => $bookings->total_road_count,
            'air' => null,
            'sea' => null,
            'train' => null
        ]; 
        
        return $this->success($data, 'Data retrived successfully');
    }
}
