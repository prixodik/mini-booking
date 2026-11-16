<?php

namespace App\Http\Resources;

use App\Models\Slot;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Slot */
class SlotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            // ISO 8601 с UTC-смещением часового пояса расписания, например 2026-11-16T09:00:00+03:00.
            'starts_at' => $this->starts_at->toIso8601String(),
            'ends_at' => $this->ends_at->toIso8601String(),
            'status' => $this->status(),
            // ID активной брони (имя и email клиента в список слотов не попадают).
            'booking_id' => $this->activeBooking?->id,
        ];
    }

    /** past важнее booked: начавшийся слот нельзя забронировать, даже если он свободен. */
    private function status(): string
    {
        if ($this->isPast()) {
            return 'past';
        }

        return $this->activeBooking ? 'booked' : 'available';
    }
}
