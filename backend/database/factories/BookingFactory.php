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
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'cancelled_at' => null,
        ];
    }

    /** Активная бронь конкретного слота (active_slot_id = slot_id). */
    public function forSlot(Slot $slot): static
    {
        return $this->state(fn () => ['slot_id' => $slot->id, 'active_slot_id' => $slot->id]);
    }
}
