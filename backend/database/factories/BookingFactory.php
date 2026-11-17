<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Slot;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Booking> */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'slot_id' => Slot::factory(),
            // Активная бронь: active_slot_id = slot_id (так её создаёт BookingService).
            // Замыкание получает атрибуты, в которых slot_id уже превращён в число.
            'active_slot_id' => fn (array $attributes) => $attributes['slot_id'],
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'cancelled_at' => null,
        ];
    }

    /** Бронь конкретного слота (по умолчанию активная). */
    public function forSlot(Slot $slot): static
    {
        return $this->state(fn () => ['slot_id' => $slot->id]);
    }

    /** Отменённая бронь: cancelled_at заполнен, active_slot_id = NULL. */
    public function cancelled(): static
    {
        return $this->state(fn () => ['cancelled_at' => now(), 'active_slot_id' => null]);
    }
}
