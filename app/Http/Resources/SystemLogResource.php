<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SystemLogResource extends JsonResource
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
            'first_name' => $this->user?->first_name,
            'last_name' => $this->user?->last_name,
            'email' => $this->user?->email,
            'activity' => $this->activity,
            'role' => $this->user?->role?->name,
            'ip_address' => $this->ip_address,
            'created_at' => $this->created_at,
        ];
    }
}
