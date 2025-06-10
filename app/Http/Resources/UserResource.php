<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'agent_id' => $this->when($this->agent_id !== null, $this->agent_id),
            'uuid' => $this->uuid,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone_number' => $this->phone_number,
            'email' => $this->email,
            'user_category' => $this->user_category,
            'address' => $this->address,
            'gender' => $this->gender,
            'nin' => $this->nin,
            'profile_photo_url' => $this->profile_photo_url,
            'next_of_kin_full_name' => $this->next_of_kin_full_name,
            'next_of_kin_phone_number' => $this->next_of_kin_phone_number,
            'next_of_kin_gender' => $this->next_of_kin_gender,
            'next_of_kin_relationship' => $this->next_of_kin_relationship,
            'email_verified' => $this->email_verified,
            'sms_verified' => $this->sms_verified,
            'status' => $this->status,
            'reason' => $this->reason,
            'date_registered' => $this->created_at,
            'avatar_url' => $this->avatar_url,
            'profile_photo' => $this->profile_photo,
            'driver_verified' => $this->whenLoaded('union', fn () => (bool) $this->driver_verified),
            'union' => $this->whenLoaded('union', fn () => $this->union?->name),
            'documents' => $this->whenLoaded('document', fn () => $this->document?->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'name' => $doc->name,
                    'type' => $doc->type,
                    'status' => $doc->status,
                    'url' => $doc->url,
                    'date_uploaded' => $doc->created_at,
                ];
            })),
            'vehicle' => $this->whenLoaded('vehicle', fn () => $this->vehicle?->map(function ($vehicle) {
                return [
                    'id' => $vehicle->id,
                    'name' => $vehicle->name,
                    'reg_no' => $vehicle->reg_no,
                    'type' => $vehicle->type,
                    'status' => $vehicle->status,
                    'date_registered' => $vehicle->created_at,
                ];
            })),
            'activities' => $this->whenLoaded('tripBooking', fn () => $this->tripBooking->map(function ($booking) {
                $trip = $booking->trip;

                return [
                    'title' => ucwords(($trip?->means ?? 'Unknown').' Trip'),
                    'desc' => 'Booked a '.($trip?->means ?? 'Unknown')
                        .' trip: '.($trip?->transitCompany?->name ?? 'Unknown Company')
                        .' - '.($trip?->departureCity?->state?->name ?? '-').' > '.($trip?->departureCity?->name ?? '-')
                        .' to '.($trip?->destinationCity?->state?->name ?? '-').' > '.($trip?->destinationCity?->name ?? '-'),
                    'date' => optional($booking->created_at)->format('M jS Y - h:iA'),
                ];
            })),
            'manifests' => $this->when($this->relationLoaded('trip') && $this->getRelation('trip')->count() > 0, $this->trip?->map(function ($t) {
                return [
                    'trip_uuid' => $t->uuid,
                    'route' => $t->departureCity->state->name.' to '.$t->destinationCity->state->name,
                    'total_people' => $t->booking->count(),
                    'date' => \Carbon\Carbon::parse($t->departure_date)->format('dS M, Y, H:iA'),
                ];
            })),
            'watchlists' => $this->when(
                $this->watchlist?->isNotEmpty(),
                $this->watchlist?->map(function ($watchlist) {
                    return [
                        'full_name' => $watchlist->full_name,
                        'phone' => $watchlist->phone,
                        'email' => $watchlist->email,
                        'dob' => $watchlist->dob,
                        'state_of_origin' => $watchlist->state_of_origin,
                        'investigation_officer' => $watchlist->investigation_officer,
                        'io_contact_number' => $watchlist->io_contact_number,
                        'alert_location' => $watchlist->alert_location,
                        'photo_url' => $watchlist->photo_url,
                        'documents' => $watchlist->documents,
                        'category' => $watchlist->category,
                        'recent_location' => $watchlist->recent_location,
                        'observation' => $watchlist->observation,
                        'status' => $watchlist->status,
                        'date' => $watchlist->created_at,
                        'updated' => $watchlist->updated_at,
                    ];
                })
            ),
        ];
    }
}
