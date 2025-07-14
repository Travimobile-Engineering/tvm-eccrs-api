<?php

namespace App\Enums;

enum IncidentCategory: string
{
    case GeneralSecurityIncident = 'General Security Incident';
    case SafetyIncidents = 'Safety Incidents';
    case TransportationSpecificIncidents = 'Transportation Specific Incidents';
    case EmergencySituations = 'Emergency Situations';
}
