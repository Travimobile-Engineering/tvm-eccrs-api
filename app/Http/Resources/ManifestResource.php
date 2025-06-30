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
        return [
            'driver' => [
                'first_name' => $this->trip?->vehicle?->driver?->first_name,
                'last_name' => $this->trip?->vehicle?->driver?->last_name,
                'phone_number' => $this->trip?->vehicle?->driver?->phone_number,
                'email' => $this->trip?->vehicle?->driver?->email,
                'address' => $this->trip?->vehicle?->driver?->address,
                'gender' => $this->trip?->vehicle?->driver?->gender,
                'nin' => $this->trip?->vehicle?->driver?->nin,
                'profile_photo_url' => $this->trip?->vehicle?->driver?->profile_photo_url,
                'next_of_kin_full_name' => $this->trip?->vehicle?->driver?->next_of_kin_full_name,
                'next_of_kin_phone_number' => $this->trip?->vehicle?->driver?->next_of_kin_phone_number,
                'next_of_kin_gender' => $this->trip?->vehicle?->driver?->next_of_kin_gender,
                'next_of_kin_relationship' => $this->trip?->vehicle?->driver?->next_of_kin_relationship,
                'status' => $this->trip?->vehicle?->driver?->status,
                'transit_company' => $this->trip?->vehicle?->driver?->transit_company,
            ],
            'vehicle' => [
                'name' => $this->trip?->vehicle?->name,
                'brand' => $this->trip?->vehicle?->brand?->name,
                'plate_no' => $this->trip?->vehicle?->plate_no,
                'capacity' => $this->trip?->vehicle?->capacity,
                'year' => $this->trip?->vehicle?->year,
                'color' => $this->trip?->vehicle?->color,
                'model' => $this->trip?->vehicle?->model,
                'insurance_status' => $this->trip?->vehicle?->driver?->documents->firstWhere('type', 'vehicle_insurance')?->status,
                'insurance_expiry' => $this->trip?->vehicle?->driver?->documents->firstWhere('type', 'vehicle_insurance')?->expiration_date,
            ],
            'documents' => $this->trip?->vehicle?->driver?->documents->map(fn ($doc) => [
                'type' => $doc->type,
                'image_url' => $doc->image_url,
                'number' => $doc->number,
                'expiration_date' => $doc->expiration_date,
                'status' => $doc->status,
            ]),
            'passengers' => $this->trip->bookings?->map(function ($booking) {
                return $booking->tripBookingPassengers->flatMap(function ($passenger) {
                    return [
                        'id' => $passenger->id,
                        'name' => $passenger->name,
                        'email' => $passenger->email,
                        'phone_number' => $passenger->phone_number,
                        'gender' => $passenger->gender,
                    ];
                });
            })->toArray(),
        ];
    }
}
