<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ZoneDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'departure' => $this->departureState?->name,
            'destination' => $this->destinationState?->name,
            'means' => $this->means,
            'passengers' => $this->bookings->count(),
            'checkedIn_passengers' => $this->bookings->sum(fn($b) => $b->confirmed_passengers_count),
            'trips' => $this->trips_count,
        ];
    }
}
