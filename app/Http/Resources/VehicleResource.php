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
            'documents' => $this->driver->documents->map(function ($document) {
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
