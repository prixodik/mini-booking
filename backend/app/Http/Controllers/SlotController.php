<?php

namespace App\Http\Controllers;

use App\Http\Resources\SlotResource;
use App\Models\Slot;

class SlotController extends Controller
{
    /** GET /api/slots */
    public function index()
    {
        $slots = Slot::with('activeBooking')->orderBy('starts_at')->get();

        return SlotResource::collection($slots)->additional([
            'meta' => [
                'timezone' => config('app.timezone'),
                'server_time' => now()->toIso8601String(),
            ],
        ]);
    }
}
