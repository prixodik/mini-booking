<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Slot;
use Database\Seeders\SlotSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class SlotSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_normalizes_slot_dates_to_utc(): void
    {
        $start = now('Europe/Moscow')->setDate(2026, 1, 5)->setTime(9, 0);
        $slot = Slot::create([
            'starts_at' => $start,
            'ends_at' => '2026-01-05T10:00:00+03:00',
        ]);

        $this->assertDatabaseHas('slots', [
            'id' => $slot->id,
            'starts_at' => '2026-01-05 06:00:00',
            'ends_at' => '2026-01-05 07:00:00',
        ]);
        $this->assertSame('+03:00', $start->format('P'));
    }

    public function test_seeder_refuses_schedule_after_timezone_change(): void
    {
        $this->setScheduleNow('2026-01-05 08:00');
        $this->seed(SlotSeeder::class);
        $starts = Slot::orderBy('id')->pluck('starts_at')->all();

        config(['booking.timezone' => 'Asia/Novosibirsk']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('php artisan migrate:fresh --seed');

        try {
            $this->seed(SlotSeeder::class);
        } finally {
            $this->assertEquals($starts, Slot::orderBy('id')->pluck('starts_at')->all());
        }
    }

    public function test_creates_seven_days_of_nine_slots_including_past_slots_of_today(): void
    {
        $this->setScheduleNow('2026-01-05 15:30');

        $this->seed(SlotSeeder::class);

        $this->assertSame(63, Slot::count());
        $first = Slot::orderBy('starts_at')->first();
        $this->assertSame('2026-01-05 09:00', $first->starts_at->setTimezone(config('booking.timezone'))->format('Y-m-d H:i'));
        $last = Slot::orderBy('starts_at', 'desc')->first();
        $this->assertSame('2026-01-11 18:00', $last->ends_at->setTimezone(config('booking.timezone'))->format('Y-m-d H:i'));
    }

    public function test_rerun_is_idempotent_and_next_day_adds_only_missing_slots(): void
    {
        $this->setScheduleNow('2026-01-05 08:00');
        $this->seed(SlotSeeder::class);
        $slot = Slot::orderBy('starts_at')->skip(3)->first();
        $booking = Booking::factory()->forSlot($slot)->create();

        $this->seed(SlotSeeder::class);
        $this->assertSame(63, Slot::count());
        $this->assertSame($slot->id, Slot::orderBy('starts_at')->skip(3)->first()->id);
        $this->assertNull($booking->fresh()->cancelled_at);

        $this->setScheduleNow('2026-01-06 08:00');
        $this->seed(SlotSeeder::class);
        $this->assertSame(72, Slot::count()); // +9 слотов нового дня, старые сохранены
        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
    }
}
