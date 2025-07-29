<?php

namespace App\Enums;

enum IncidentCategory: string
{
    case GeneralSecurityIncident = 'general-security-incident';
    case SafetyIncidents = 'safety-incidents';
    case TransportationSpecificIncidents = 'transportation-specific-incidents';
    case EmergencySituations = 'emergency-situations';
}
