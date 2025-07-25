<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
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
            'name' => $this->name,
            'company' => $this->company->name,
            'brand' => $this->brand?->name,
            'ac' => $this->ac,
            'plate_no' => $this->plate_no,
            'engine_no' => $this->engine_no,
            'chassis_no' => $this->chassis_no,
            'type' => $this->type,
            'capacity' => $this->capacity,
            'year' => $this->year,
            'color' => $this->color,
            'model' => $this->model,
            'air_conditioned' => $this->air_conditioned,
            'seats' => $this->seats,
            'seat_row' => $this->seat_row,
            'seat_column' => $this->seat_column,
            'description' => $this->description,
            'status' => ['unverified', 'verified'][$this->status],
            'date_created' => $this->created_at,
            'driver' => [
                'first_name' => $this->driver->first_name,
                'last_name' => $this->driver->last_name,
                'phone_number' => $this->driver->phone_number,
                'email' => $this->driver->email,
                'user_category' => $this->driver->user_category,
                'address' => $this->driver->address,
                'gender' => $this->driver->gender,
                'nin' => $this->driver->nin,
                'profile_photo_url' => $this->driver->profile_photo_url,
                'next_of_kin_full_name' => $this->driver->next_of_kin_full_name,
                'next_of_kin_phone_number' => $this->driver->next_of_kin_phone_number,
                'next_of_kin_gender' => $this->driver->next_of_kin_gender,
                'next_of_kin_relationship' => $this->driver->next_of_kin_relationship,
                'status' => $this->driver->status,
                'date_registered' => $this->driver->created_at,
            ],
            'documents' => $this->driver->documents?->map(function ($document) {
                return [
                    'type' => $document->type,
                    'image_url' => $document->image_url,
                    'public_id' => $document->public_id,
                    'number' => $document->number,
                    'expiration_date' => $document->expiration_date,
                    'status' => $document->status,
                    'uploaded_on' => $document->created_at,
                ];
            }),
        ];
    }
}
