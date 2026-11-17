<?php

use Illuminate\Support\Facades\Route;

// Backend — только JSON API; интерфейс — во frontend/.
Route::get('/', fn () => response()->json(['name' => 'Mini Booking API', 'slots' => url('/api/slots')]));
