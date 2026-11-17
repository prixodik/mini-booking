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

    public function test_returns_slots_from_start_of_today_only(): void
    {
        $tz = config('app.timezone');
        // setUp засеял расписание 5 января; на следующий день Seeder запускается снова.
        Carbon::setTestNow(Carbon::parse('2026-01-06 08:00:00', $tz));
        $this->seed(SlotSeeder::class);
        Carbon::setTestNow(Carbon::parse('2026-01-06 10:30:00', $tz));

        $data = $this->getJson('/api/slots')->assertOk()->json('data');

        $this->assertCount(63, $data);
        $this->assertSame('2026-01-06T09:00:00+03:00', $data[0]['starts_at']);
        $this->assertSame('2026-01-12T18:00:00+03:00', end($data)['ends_at']);
        $this->assertSame('past', $data[0]['status']);
        $this->assertSame('past', $data[1]['status']);
        $this->assertSame('available', $data[2]['status']);
        $this->assertEmpty(array_filter($data, fn ($slot) => str_starts_with($slot['starts_at'], '2026-01-05')));
    }

    public function test_default_factory_booking_marks_slot_booked_and_cancelled_one_does_not(): void
    {
        $tz = config('app.timezone');
        $bookedSlot = Slot::where('starts_at', Slot::toDbValue(Carbon::parse('2026-01-05 12:00', $tz)))->first();
        $freeSlot = Slot::where('starts_at', Slot::toDbValue(Carbon::parse('2026-01-05 13:00', $tz)))->first();

        $active = Booking::factory()->forSlot($bookedSlot)->create();
        $cancelled = Booking::factory()->forSlot($freeSlot)->cancelled()->create();

        $this->assertSame($bookedSlot->id, $active->active_slot_id);
        $this->assertNull($active->cancelled_at);
        $this->assertNull($cancelled->active_slot_id);
        $this->assertNotNull($cancelled->cancelled_at);

        $rows = collect($this->getJson('/api/slots')->json('data'))->keyBy('id');
        $this->assertSame('booked', $rows[$bookedSlot->id]['status']);
        $this->assertSame($active->id, $rows[$bookedSlot->id]['booking_id']);
        $this->assertSame('available', $rows[$freeSlot->id]['status']);
        $this->assertNull($rows[$freeSlot->id]['booking_id']);
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
