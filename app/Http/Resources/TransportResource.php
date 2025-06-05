<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            "id" => $this->id,
            "name" => $this->name,
            "short_name" => $this->short_name,
            "reg_no" => $this->reg_no,
            "url" => $this->url,
            "email" => $this->email,
            "country_code" => $this->country_code,
            "state" => $this->state,
            "lga" => $this->lga,
            "phone" => $this->phone,
            "address" => $this->address,
            "about_details" => $this->about_details,
            "union" => $this->union?->name,
            "union_states_chapter" => $this->unionState->name,
            "park" => $this->park,
            "type" => $this->type,
            "date_registered" => $this->created_at,
            "status" => ["unverified", "verified"][$this->ev],
            "vehicles" => $this->vehicles->count(),
            "drivers" => $this->when($this->relationLoaded('drivers'), fn() => $this->getRelation('drivers')->count()),
            "bookings" => $this->when($this->relationLoaded('bookings'), fn() => $this->bookings->count()),
            "staffs" => $this->when($this->relationLoaded('drivers'), $this->drivers->map(function($driver){
                    return [
                        "first_name" => $driver->driver->first_name,
                        "last_name" => $driver->driver->last_name,
                        "phone_number" => $driver->driver->phone_number,
                        "email" => $driver->driver->email,
                        "profile_photo_url" => $driver->driver->profile_photo_url,
                        "status" => $driver->driver->status,
                        "date_registered" => $driver->driver->created_at,
                    ];
                })
            ),
        ];
        return $data;
    }
}
