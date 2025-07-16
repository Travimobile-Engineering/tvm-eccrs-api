<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone_number,
            'nin' => $this->nin,
            'image' => $this->profile_photo,
            'role' => $this->whenLoaded('roles', function () {
                return $this->roles ? $this->roles()->pluck('name')->first() : null;
            }),
            'zone' => $this->whenLoaded('zoneModel', function () {
                return [
                    'id' => $this->zoneModel->id,
                    'name' => $this->zoneModel->name,
                ];
            }),
            'state' => $this->whenLoaded('stateModel', function () {
                return [
                    'id' => $this->stateModel->id,
                    'name' => $this->stateModel->name,
                ];
            }),
            'organization' => $this->whenLoaded('organization', function () {
                return [
                    'id' => $this->organization->id,
                    'name' => $this->organization->name,
                ];
            }),
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
