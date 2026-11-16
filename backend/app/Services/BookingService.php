<?php

namespace App\Services;

use App\Exceptions\BookingNotFoundException;
use App\Exceptions\SlotUnavailableException;
use App\Models\Booking;
use App\Models\Slot;
use Illuminate\Database\UniqueConstraintViolationException;

/**
 * Единственное место, где создаются и отменяются брони, а значит и ведётся
 * поле bookings.active_slot_id (slot_id у активной брони, NULL у отменённой).
 */
class BookingService
{
    public function create(Slot $slot, string $name, string $email): Booking
    {
        if ($slot->isPast()) {
            throw SlotUnavailableException::past();
        }

        try {
            // Решающая проверка — UNIQUE(active_slot_id) в БД: при гонке двух запросов
            // вторая вставка нарушит индекс. Предварительный exists() не нужен.
            return Booking::create([
                'slot_id' => $slot->id,
                'customer_name' => $name,
                'customer_email' => $email,
                'active_slot_id' => $slot->id,
            ]);
        } catch (UniqueConstraintViolationException) {
            throw SlotUnavailableException::taken();
        }
    }

    public function cancel(int $bookingId): void
    {
        // Атомарный условный апдейт: из двух одновременных отмен успешна только одна.
        $updated = Booking::whereKey($bookingId)
            ->whereNull('cancelled_at')
            ->update(['cancelled_at' => now(), 'active_slot_id' => null]);

        if ($updated === 1) {
            return;
        }

        throw Booking::whereKey($bookingId)->exists()
            ? BookingNotFoundException::alreadyCancelled()
            : BookingNotFoundException::unknown();
    }
}
