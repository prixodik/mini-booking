<?php

namespace App\Http\Controllers;

use App\Exceptions\BookingNotFoundException;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Slot;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BookingController extends Controller
{
    public function __construct(private readonly BookingService $bookings)
    {
    }

    /** POST /api/bookings: 201 | 409 (занят/прошёл) | 422 (валидация). */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        $data = $request->validated();

        $booking = $this->bookings->create(
            Slot::findOrFail($data['slot_id']),
            $data['customer_name'],
            $data['customer_email'],
        );

        return response()->json(['data' => ['id' => $booking->id]], 201);
    }

    /**
     * DELETE /api/bookings/{id}: 204 | 404 (нет брони или уже отменена).
     * ID приходит строкой: маршрут пропускает любые цифры, а число больше PHP_INT_MAX нельзя привести к int.
     */
    public function destroy(string $id): Response
    {
        // ID из цифр, но больше PHP_INT_MAX: такой брони быть не может.
        $bookingId = filter_var($id, FILTER_VALIDATE_INT);
        if ($bookingId === false) {
            throw BookingNotFoundException::unknown();
        }

        $this->bookings->cancel($bookingId);

        return response()->noContent();
    }
}
