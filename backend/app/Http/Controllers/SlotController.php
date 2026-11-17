<?php

namespace App\Http\Controllers;

use App\Http\Resources\SlotResource;
use App\Models\Slot;

class SlotController extends Controller
{
    /** GET /api/slots */
    public function index()
    {
        $timezone = config('booking.timezone');
        // С начала сегодняшнего дня по времени расписания: прошедшие слоты сегодня нужны (ТЗ), прошлые дни — нет.
        $slots = Slot::with('activeBooking')
            ->startingFrom(now($timezone)->startOfDay())
            ->orderBy('starts_at')
            ->get();

        return SlotResource::collection($slots)->additional([
            'meta' => [
                'timezone' => $timezone,
                'server_time' => now($timezone)->toIso8601String(),
            ],
        ]);
    }
}
