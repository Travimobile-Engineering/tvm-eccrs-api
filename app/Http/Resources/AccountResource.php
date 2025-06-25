<?php

namespace App\Http\Resources;

use App\Libraries\Utility;
use App\Models\Suspension;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge([
            'id' => $this->id,
            'user_id' => $this->unique_id,
            'name' => "{$this->first_name} {$this->last_name}",
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'nin' => Utility::decrypt($this->nin, config('security.encoding_key')),
            'role' => $this->role?->name,
            'zone' => $this->zone?->name,
            'state' => $this->state?->name,
            'organization' => $this->organization?->name,
            'image' => $this->profile_photo,
            'status' => $this->status,
            'created_at' => $this->created_at->format('d-m-Y H:i:s'),
        ], $this->getSuspensionStatus());
    }

    private function getSuspensionStatus(): array
    {
        $suspension = Suspension::where('user_id', $this->id)
            ->whereNull('lifted_at')
            ->latest()
            ->first();

        return [
            'suspension_reason' => $suspension?->reason,
            'suspension_duration' => $suspension?->end_date,
            'suspension_explanation' => $suspension?->explanation,
        ];
    }
}
