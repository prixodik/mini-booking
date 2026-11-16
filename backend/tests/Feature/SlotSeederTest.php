<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Slot;
use Database\Seeders\SlotSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SlotSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_creates_seven_days_of_nine_slots_including_past_slots_of_today(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-01-05 15:30:00', config('app.timezone')));

        $this->seed(SlotSeeder::class);

        $this->assertSame(63, Slot::count());
        $first = Slot::orderBy('starts_at')->first();
        $this->assertSame('2026-01-05 09:00', $first->starts_at->format('Y-m-d H:i'));
        $last = Slot::orderBy('starts_at', 'desc')->first();
        $this->assertSame('2026-01-11 18:00', $last->ends_at->format('Y-m-d H:i'));
    }

    public function test_rerun_is_idempotent_and_next_day_adds_only_missing_slots(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-01-05 08:00:00', config('app.timezone')));
        $this->seed(SlotSeeder::class);
        $slot = Slot::orderBy('starts_at')->skip(3)->first();
        $booking = Booking::factory()->forSlot($slot)->create();

        $this->seed(SlotSeeder::class);
        $this->assertSame(63, Slot::count());
        $this->assertSame($slot->id, Slot::orderBy('starts_at')->skip(3)->first()->id);
        $this->assertNull($booking->fresh()->cancelled_at);

        Carbon::setTestNow(Carbon::parse('2026-01-06 08:00:00', config('app.timezone')));
        $this->seed(SlotSeeder::class);
        $this->assertSame(72, Slot::count()); // +9 слотов нового дня, старые сохранены
        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
    }
}
