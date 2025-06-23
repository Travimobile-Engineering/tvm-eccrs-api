<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ManifestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $passengers = collect();
        $this->trip->bookings->map(function($booking)  use($passengers){
            
            $passengers->push([
                'first_name' => $booking->user->first_name,
                'last_name' => $booking->user->last_name,
                'phone_number' => $booking->user->phone_number,
                'email' => $booking->user->email,
                'address' => $booking->user->address,
                'gender' => $booking->user->gender,
                'nin' => $booking->user->nin,
                'profile_photo_url' => $booking->user->profile_photo_url,
                'next_of_kin_full_name' => $booking->user->next_of_kin_full_name,
                'next_of_kin_phone_number' => $booking->user->next_of_kin_phone_number,
                'next_of_kin_gender' => $booking->user->next_of_kin_gender,
                'next_of_kin_relationship' => $booking->user->next_of_kin_relationship,
                'status' => $booking->user->status,
            ]);

            if($booking->travelling_with){
                collect(json_decode($booking->travelling_with))->each(function($t) use($passengers){
                    $passengers->push([
                        "first_name" => explode(' ', $t->name)[0],
                        "last_name" => explode(' ', $t->name)[1] ?? '',
                        "email" => $t->email,
                        "phone_number" => $t->phone_number,
                        "gender" => $t->gender,
                        "nin" => $t->nin,
                        "next_of_kin_full_name" => $t->next_of_kin_full_name,
                        "next_of_kin_relationship" => $t->next_of_kin_relationship,
                        "next_of_kin_phone_number" => $t->next_of_kin_phone_number,
                    ]);
                });
            }
        });
        
        return [
            'driver' => [
                'first_name' => $this->trip->vehicle->driver->first_name,
                'last_name' => $this->trip->vehicle->driver->last_name,
                'phone_number' => $this->trip->vehicle->driver->phone_number,
                'email' => $this->trip->vehicle->driver->email,
                'address' => $this->trip->vehicle->driver->address,
                'gender' => $this->trip->vehicle->driver->gender,
                'nin' => $this->trip->vehicle->driver->nin,
                'profile_photo_url' => $this->trip->vehicle->driver->profile_photo_url,
                'next_of_kin_full_name' => $this->trip->vehicle->driver->next_of_kin_full_name,
                'next_of_kin_phone_number' => $this->trip->vehicle->driver->next_of_kin_phone_number,
                'next_of_kin_gender' => $this->trip->vehicle->driver->next_of_kin_gender,
                'next_of_kin_relationship' => $this->trip->vehicle->driver->next_of_kin_relationship,
                'status' => $this->trip->vehicle->driver->status,
                'transit_company' => $this->trip->vehicle->driver->transit_company,
            ],
            'vehicle' => [
                'name' => $this->trip->vehicle->name,
                'brand' => $this->trip->vehicle->brand?->name,
                'plate_no' => $this->trip->vehicle->plate_no,
                'capacity' => $this->trip->vehicle->capacity,
                'year' => $this->trip->vehicle->year,
                'color' => $this->trip->vehicle->color,
                'model' => $this->trip->vehicle->model,
            ],
            'documents' => $this->trip->vehicle->driver->documents->map(fn($doc) => [
                'type' => $doc->type,
                'image_url' => $doc->image_url,
                'number' => $doc->number,
                'expiration_date' => $doc->expiration_date,
                'status' => $doc->status,
            ]),
            'passengers' => $passengers
        ];
    }
}
