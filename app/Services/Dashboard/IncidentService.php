<?php

namespace App\Services\Dashboard;

use App\Models\Incident;
use App\Traits\HttpResponse;
use App\Enums\IncidentCategory;
use Illuminate\Support\Facades\DB;

class IncidentService
{

    use HttpResponse;

    public function getData(){

        $hotspots = Incident::selectRaw('COUNT(*) as total, states.name')
            ->join('states', 'incidents.state_id', '=', 'states.id')
            ->groupBy('states.name')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        $sql = [];
        $months = 6;
        $monthsName = [];
        $incidentCategories = IncidentCategory::cases();

        for($i=0; $i<$months; $i++){
            $month = now()->submonths($i);
            $monthStart = $month->clone()->startOfMonth();
            $monthEnd = $month->clone()->endOfMonth();
            $month_name = $month->monthName;
            $monthsName[] = strtolower($month_name);
            $range = "date >= DATE('$monthStart') AND date <= DATE('$monthEnd')";
            
            foreach ($incidentCategories as $category) {
                $alias = str_replace('-', '_', strtolower($month_name . '_' .$category->value. '_count'));
                $sql[] = "COUNT(CASE WHEN category = '{$category->value}' AND $range THEN 1 END) AS $alias";
            }
        }

        $trends = Incident::selectRaw(implode(', ', $sql))
            ->first();

        $date = now()->today();
        if(request('date')){
            $date = request('date');
        }
        $live_incidents = Incident::join('states', 'incidents.state_id', '=', 'states.id')
            ->where('date', DB::raw("Date('$date')"))
            ->select('incidents.id', DB::raw('CONCAT(incidents.date, " ", incidents.time) as date'), 'states.name as state', 'incidents.category')
            ->get();

        $data = [];

        foreach($hotspots as $hotspot){
            $data['hotspots'][$hotspot->name] = $hotspot->total;
        }

        foreach($monthsName as $month){

            foreach ($incidentCategories as $category) {
                $key = str_replace('-', '_', strtolower($category->value));
                $data[$month][$key] = $trends->{"{$month}_{$key}_count"} ?? 0;
            }
        }

        $data[ 'live_incidents'] = $live_incidents;
        
        return $this->success($data, "hotspots retrieved successfully");
    }

    public function getIncidentDetail($id){
        $incident = Incident::where('incidents.id', $id)
            ->join('states', 'states.id', '=', 'state_id')
            ->select('status', 'category', 'type', DB::raw("CONCAT(date, ' ', time) as date"), DB::raw("CONCAT(city, ', ', name) as location"), 'description', 'severity_level', 'persons_of_interest')
            ->first();

        if(! $incident){
            return $this->error(null, "Resource not found", 404);
        }

        return $this->success($incident, 'Incident detail retrieved successfully');
    }
}
