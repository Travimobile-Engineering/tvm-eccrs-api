<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WatchlistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $this->userByNin ?? $this->userByPhone ?? $this->userByEmail;
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'dob' => $this->dob,
            'state_of_origin' => $this->state_of_origin,
            'nin' => $this->nin,
            'investigation_officer' => $this->investigation_officer,
            'io_contact_number' => $this->io_contact_number,
            'alert_location' => $this->alert_location,
            'reason' => $this->reason,
            'photo_url' => $this->photo_url,
            'documents' => $this->documents,
            'category' => $this->category,
            'recent_location' => $this->recent_location,
            'observation' => $this->observation,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'recent_activities' => [
                'road_trips' => $user->tripBookings->map(fn($booking) =>[
                    'company' => $booking->trip->transitCompany->name,
                    'from' => [
                        'state' => $booking->trip->departureState->name,
                        'city' => $booking->trip->departureCity->name,
                    ],
                    'to' => [
                        'state' => $booking->trip->destinationState->name,
                        'city' => $booking->trip->destinationCity->name,
                    ],
                    'date' => $booking->created_at,
                ]),
            ],
        ];
    }
}
