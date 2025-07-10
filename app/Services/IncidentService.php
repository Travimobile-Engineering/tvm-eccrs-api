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
        return IncidentResource::collection($incidents);
    }

    public function getIncidentStats(){
        $incidents = Incident::all();

        $curMonthStart = now()->startOfMonth();
        $curMonthEnd = now()->endOfMonth();
        $previousMonthStart = now()->subMonth()->startOfMonth();
        $previousMonthEnd = now()->subMonth()->endOfMonth();

        $prevMonthIncidents = $incidents->filter(function ($incident) use ($previousMonthStart, $previousMonthEnd) {
            return $incident->date >= $previousMonthStart && $incident->date <= $previousMonthEnd;
        })->count();

        $curMonthIncidents = $incidents->filter(function ($incident) use ($curMonthStart, $curMonthEnd) {
            return $incident->date >= $curMonthStart && $incident->date <= $curMonthEnd;
        })->count();

        $incidentsByLocation = $incidents->groupBy('location')->sortByDesc(function ($group) {
            return $group->count();
        });

        $months = [];
        // Get the last 6 months including the current month
        for ($i = 0; $i <= 5; $i++) {
            
            $month = now()->subMonths($i);
            $startOfMonth = clone($month)->startOfMonth();
            $endOfMonth = clone($month)->endOfMonth();

            $totalIncidents = $incidents->filter(fn($incident) => $incident->date >= $startOfMonth && $incident->date <= $endOfMonth)->count();
            $generalSecurityIncidents = $incidents->filter(fn($incident) => $incident->date >= $startOfMonth && $incident->date <= $endOfMonth && $incident->category === IncidentCategory::GeneralSecurityIncident->value)->count();
            $safetyIncidents = $incidents->filter(fn($incident) => $incident->date >= $startOfMonth && $incident->date <= $endOfMonth && $incident->category === IncidentCategory::SafetyIncidents->value)->count();
            $transportationViolations = $incidents->filter(fn($incident) => $incident->date >= $startOfMonth && $incident->date <= $endOfMonth && $incident->category === IncidentCategory::TransportationSpecificIncidents->value)->count();
            $emergencySituations = $incidents->filter(fn($incident) => $incident->date >= $startOfMonth && $incident->date <= $endOfMonth && $incident->category === IncidentCategory::EmergencySituations->value)->count();
            
            $months[$month->monthName] = [
                'total' => $totalIncidents,
                'general_security_incident' => [
                    'count' => $generalSecurityIncidents,
                    'percentage' => calculatePercentageOf($generalSecurityIncidents, $totalIncidents),
                ],
                'safety_incidents' => [
                    'count' => $safetyIncidents,
                    'percentage' => calculatePercentageOf($safetyIncidents, $totalIncidents),
                ],
                'transportation_voiolation' => [
                    'count' => $transportationViolations,
                    'percentage' => calculatePercentageOf($transportationViolations, $totalIncidents),
                ],
                'emergency_situation' => [
                    'count' => $emergencySituations,
                    'percentage' => calculatePercentageOf($emergencySituations, $totalIncidents),
                ],
            ];
        }
        
        $data = [
            'total' => $incidents->count(),
            'percentage' => calculatePercentageOf($curMonthIncidents, $prevMonthIncidents),
            'most_common_location' => [
                'name' => array_key_first($incidentsByLocation->toArray()),
                'percentage' => calculatePercentageOf($incidentsByLocation->first()->count(), $incidents->count()),],
            'monthly_stats' => $months,
        ];

        return $this->success($data, 'Incident statistics retrieved successfully.');
    }
}
