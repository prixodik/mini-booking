<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Slot;
use Database\Seeders\SlotSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SlotsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-01-05 10:00:00', config('app.timezone')));
        $this->seed(SlotSeeder::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_lists_slots_sorted_with_statuses_offset_and_without_personal_data(): void
    {
        $tz = config('app.timezone');
        $future = Slot::where('starts_at', Slot::toDbValue(Carbon::parse('2026-01-05 12:00', $tz)))->first();
        Booking::factory()->forSlot($future)->create(['customer_name' => 'Секрет', 'customer_email' => 'secret@example.com']);

        $response = $this->getJson('/api/slots')->assertOk();

        $data = $response->json('data');
        $this->assertCount(63, $data);
        $starts = array_column($data, 'starts_at');
        $sorted = $starts;
        usort($sorted, fn ($a, $b) => strtotime($a) <=> strtotime($b));
        $this->assertSame($sorted, $starts);

        $byStart = collect($data)->keyBy('starts_at');
        $this->assertSame('past', $byStart['2026-01-05T09:00:00+03:00']['status']);
        $this->assertSame('past', $byStart['2026-01-05T10:00:00+03:00']['status']); // начало == сейчас
        $this->assertSame('available', $byStart['2026-01-05T11:00:00+03:00']['status']);
        $booked = $byStart['2026-01-05T12:00:00+03:00'];
        $this->assertSame('booked', $booked['status']);
        $this->assertNotNull($booked['booking_id']);
        $this->assertSame('2026-01-05T13:00:00+03:00', $booked['ends_at']);

        $response->assertJsonPath('meta.timezone', 'Europe/Moscow');
        $this->assertStringNotContainsString('secret', $response->getContent());
        $this->assertStringNotContainsString('Секрет', $response->getContent());
    }

    public function test_cancelled_booking_makes_future_slot_available_again(): void
    {
        $slot = Slot::where('starts_at', Slot::toDbValue(Carbon::parse('2026-01-05 12:00', config('app.timezone'))))->first();
        $booking = Booking::factory()->forSlot($slot)->create();
        $this->deleteJson("/api/bookings/{$booking->id}")->assertNoContent();

        $row = collect($this->getJson('/api/slots')->json('data'))->firstWhere('id', $slot->id);

        $this->assertSame('available', $row['status']);
        $this->assertNull($row['booking_id']);
    }
}
