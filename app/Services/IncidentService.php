<?php

namespace App\Services;

use App\Models\Incident;
use App\Traits\HttpResponse;
use App\Enums\IncidentCategory;
use App\Http\Resources\IncidentResource;

class IncidentService
{
    use HttpResponse;

    public function getIncidents()
    {
        $incidents = Incident::when(request('search'), function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('category', 'like', "%$search%")
                    ->orWhere('type', 'like', "%$search%")
                    ->orWhere('location', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
            });
        })
        ->when(request('date'), function ($query, $date) {
            $query->whereDate('date', $date);
        })
        ->paginate(25);
        return $this->withPagination($incidents->toResourceCollection());
    }

    public function getIncidentStats(){
        // Query for last 6 months incident including the current month
        $query = Incident::where('date', '>=', now()->subMonths(5)->startOfMonth());
        $allIncidents = Incident::count();

        $curMonthStart = now()->startOfMonth();
        $curMonthEnd = now()->endOfMonth();
        $previousMonthStart = now()->subMonth()->startOfMonth();
        $previousMonthEnd = now()->subMonth()->endOfMonth();

        $prevMonthIncidents = clone($query)->whereBetween('date', [$previousMonthStart, $previousMonthEnd])->count();
        $curMonthIncidents = clone($query)->whereBetween('date', [$curMonthStart, $curMonthEnd])->count();

        $incidentsByLocation = clone($query)
            ->selectRaw('location, COUNT(*) as count')
            ->groupBy('location')
            ->orderByDesc('count')
            ->get();
    
        $totalIncidentsInSixMonths = clone($query)->count();

        $months = [];
        collect([0, 1, 2, 3, 4, 5])
        ->each(function ($i) use ($query, &$months, $totalIncidentsInSixMonths) 
        {
            
            $month = now()->subMonths($i);
            $startOfMonth = clone($month)->startOfMonth();
            $endOfMonth = clone($month)->endOfMonth();

            $incidentsByCategory = $query->selectRaw('category, COUNT(*) as count')
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->groupBy('category')
                ->get();

            $generalSecurityIncidents = $incidentsByCategory->firstWhere('category', IncidentCategory::GeneralSecurityIncident->value)->count ?? 0;
            $safetyIncidents = $incidentsByCategory->firstWhere('category', IncidentCategory::SafetyIncidents->value)->count ?? 0;
            $transportationViolations = $incidentsByCategory->firstWhere('category', IncidentCategory::TransportationSpecificIncidents->value)->count ?? 0;
            $emergencySituations = $incidentsByCategory->firstWhere('category', IncidentCategory::EmergencySituations->value)->count ?? 0;

            $months[$month->monthName] = [
                'total' => $totalIncidentsInSixMonths,
                'general_security_incident' => [
                    'count' => $generalSecurityIncidents,
                    'percentage' => calculatePercentageOf($generalSecurityIncidents, $totalIncidentsInSixMonths),
                ],
                'safety_incidents' => [
                    'count' => $safetyIncidents,
                    'percentage' => calculatePercentageOf($safetyIncidents, $totalIncidentsInSixMonths),
                ],
                'transportation_voiolation' => [
                    'count' => $transportationViolations,
                    'percentage' => calculatePercentageOf($transportationViolations, $totalIncidentsInSixMonths),
                ],
                'emergency_situation' => [
                    'count' => $emergencySituations,
                    'percentage' => calculatePercentageOf($emergencySituations, $totalIncidentsInSixMonths),
                ],
            ];
        });

        $thisMonthIncidents = clone($query)->whereMonth('date', now()->month)->paginate((5));
        
        $data = [
            'total' => $allIncidents,
            'percentage' => calculatePercentageOf($curMonthIncidents, $prevMonthIncidents),
            'most_common_location' => [
                'name' => $incidentsByLocation->first()->location ?? 'N/A',
                'percentage' => calculatePercentageOf($incidentsByLocation->first()->count, $allIncidents),
            ],
            'monthly_stats' => $months,
            'current_month_incidents' => IncidentResource::collection($thisMonthIncidents),,
        ];

        return $this->success($data, 'Incident statistics retrieved successfully.');
    }
}
