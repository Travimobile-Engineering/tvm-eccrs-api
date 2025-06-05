<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripResource extends JsonResource
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
            "trip_duration" => $this->trip_duration,
            "status" => $this->status,
            "departure_date" => $this->departure_date,
            "departure_time" => $this->departure_time,
            "departure" => [
                "state" => $this->departureCity->state->name,
                "city" => $this->departureCity->name,
            ] ,
            "destination" =>[
                "state" => $this->destinationCity->state->name,
                "city" => $this->destinationCity->name,
            ] ,
            "vehicle" => [
                "name" => $this->vehicle->name,
                "brand" => $this->vehicle->brand?->name,
                "plate_no" => $this->vehicle->plate_no,
                "capacity" => $this->vehicle->capacity,
                "model" => $this->vehicle->model,
            ],
            "driver" => [
                "first_name" => $this->vehicle->driver->first_name,
                "last_name" => $this->vehicle->driver->last_name,
                "profile_photo_url" => $this->vehicle->driver->profile_photo_url,
                "profile_photo" => $this->vehicle->driver->profile_photo,
                
            ]
        ];
    }
}
