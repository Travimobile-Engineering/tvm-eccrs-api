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
            "id" => $this->id,
            "agent_id" => $this->when(!empty($this->agent_id), $this->agent_id),
            "uuid" => $this->uuid,
            "first_name" => $this->first_name,
            "last_name" => $this->last_name,
            "phone_number" => $this->phone_number,
            "email" => $this->email,
            "user_category" => $this->user_category,
            "address" => $this->address,
            "gender" => $this->gender,
            "nin" => $this->nin,
            "profile_photo_url" => $this->profile_photo_url,
            "next_of_kin_full_name" => $this->next_of_kin_full_name,
            "next_of_kin_phone_number" => $this->next_of_kin_phone_number,
            "next_of_kin_gender" => $this->next_of_kin_gender,
            "next_of_kin_relationship" => $this->next_of_kin_relationship,
            "email_verified" => $this->email_verified,
            "sms_verified" => $this->sms_verified,
            "status" => $this->status,
            "reason" => $this->reason,
            "date_registered" => $this->created_at,
            "avatar_url" => $this->avatar_url,
            "profile_photo" => $this->profile_photo,
            "driver_verified" => $this->whenLoaded('union', [false, true][$this->driver_verified]),
            "union" => $this->when($this->relationLoaded('union') && !is_null($this->getRelation('union')), $this->union?->name),
            "documents" => $this->when($this->relationLoaded('document') && !is_null($this->getRelation('document')), $this->document?->map(function($doc){
                return [
                    "type" => $doc->type,
                    "number" => $doc->number,
                    "expiration_date" => $doc->expiration_date,
                    "image_url" => $doc->image_url,
                    "status" => $doc->status
                ];
            })),
            "vehicle" => $this->when($this->relationLoaded('vehicle') && !is_null($this->getRelation('vehicle')), function(){
                return [
                    "name" => $this->vehicle?->name,
                    'brand' => $this->vehicle?->brand?->name,
                    "ac" => $this->vehicle?->ac,
                    "plate_no" => $this->vehicle?->plate_no,
                    "engine_no" => $this->vehicle?->engine_no,
                    "chassis_no" => $this->vehicle?->chassis_no,
                    "type" => $this->vehicle?->type,
                    "capacity" => $this->vehicle?->capacity,
                    "year" => $this->vehicle?->year,
                    "color" => $this->vehicle?->color,
                    "model" => $this->vehicle?->model,
                    "air_conditioned" => $this->vehicle?->air_conditioned,
                    "seats" => $this->vehicle?->seats,
                    "seat_row" => $this->vehicle?->seat_row,
                    "seat_column" => $this->vehicle?->seat_column,
                    "description" => $this->vehicle?->description,
                    "status" => $this->vehicle?->status,
                ];
            }),
            "activities" => $this->when(
                $this->tripBooking?->trip && $this->tripBooking?->trip->isNotEmpty(), 
                $this->tripbooking?->map(function($booking){
                    return [
                        "title" => ucwords($booking->trip?->means . " Trip"),
                        "desc" => "Booked a ".$booking->trip?->means 
                            . " trip: ".$booking->trip?->transitCompany->name 
                            . " - " .$booking->trip?->departureCity->state->name. ">" .$booking->trip?->departureCity->name
                            ." to "
                            . $booking->trip?->destinationCity->state->name. ">" .$booking->trip?->destinationCity->name,
                        "date" => \Carbon\Carbon::parse($booking->created_at)->format('M dS Y - H:iA'),
                    ];
                })
            ),
            "manifests" => $this->when($this->relationLoaded('trip') && $this->getRelation('trip')->count() > 0, $this->trip?->map(function($t){
                return [
                    "trip_uuid" => $t->uuid,
                    "route" => $t->departureCity->state->name .' to '. $t->destinationCity->state->name,
                    "total_people" => $t->booking->count(),
                    "date" => \Carbon\Carbon::parse($t->departure_date)->format('dS M, Y, H:iA'),
                ];
            })),
            "watchlists" => $this->when(
                $this->watchlist?->isNotEmpty(), 
                $this->watchlist?->map(function($watchlist){
                    return [
                        "full_name" => $watchlist->full_name,
                        "phone" => $watchlist->phone,
                        "email" => $watchlist->email,
                        "dob" => $watchlist->dob,
                        "state_of_origin" => $watchlist->state_of_origin,
                        "investigation_officer" => $watchlist->investigation_officer,
                        "io_contact_number" => $watchlist->io_contact_number,
                        "alert_location" => $watchlist->alert_location,
                        "photo_url" => $watchlist->photo_url,
                        "documents" => $watchlist->documents,
                        "category" => $watchlist->category,
                        "recent_location" => $watchlist->recent_location,
                        "observation" => $watchlist->observation,
                        "status" => $watchlist->status,
                        "date" => $watchlist->created_at,
                        "updated" => $watchlist->updated_at,
                    ];
                })
            )
        ];
    }
}
