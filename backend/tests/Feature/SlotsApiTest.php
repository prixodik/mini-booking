<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Slot;
use Database\Seeders\SlotSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SlotsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setScheduleNow('2026-01-05 10:00');
        $this->seed(SlotSeeder::class);
    }

    public function test_lists_slots_sorted_with_statuses_offset_and_without_personal_data(): void
    {
        $future = $this->slotAt('2026-01-05 12:00');
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
        // setUp засеял расписание 5 января; на следующий день Seeder запускается снова.
        $this->setScheduleNow('2026-01-06 08:00');
        $this->seed(SlotSeeder::class);
        $this->setScheduleNow('2026-01-06 10:30');

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
        $bookedSlot = $this->slotAt('2026-01-05 12:00');
        $freeSlot = $this->slotAt('2026-01-05 13:00');

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
        $slot = $this->slotAt('2026-01-05 12:00');
        $booking = Booking::factory()->forSlot($slot)->create();
        $this->deleteJson("/api/bookings/{$booking->id}")->assertNoContent();

        $row = collect($this->getJson('/api/slots')->json('data'))->firstWhere('id', $slot->id);

        $this->assertSame('available', $row['status']);
        $this->assertNull($row['booking_id']);
    }

    // --- Время в БД: один пояс (UTC) ---

    public function test_natural_where_query_on_starts_at_is_correct(): void
    {
        $this->setScheduleNow('2026-01-05 09:30');

        // Из 63 слотов не входит только слот 09:00 сегодня: обычный запрос без ручного перевода пояса.
        $this->assertSame(62, Slot::where('starts_at', '>', now())->count());
    }

    public function test_slots_and_timestamps_are_stored_in_utc(): void
    {
        $this->setScheduleNow('2026-01-05 08:15');
        $slot = $this->slotAt('2026-01-05 09:00');
        $id = $this->postJson('/api/bookings', [
            'slot_id' => $slot->id,
            'customer_name' => 'Иван',
            'customer_email' => 'ivan@example.com',
        ])->assertCreated()->json('data.id');

        // 09:00 Москвы = 06:00 UTC; бронь создана в 08:15 Москвы = 05:15 UTC.
        $this->assertSame('2026-01-05 06:00:00', DB::table('slots')->where('id', $slot->id)->value('starts_at'));
        $this->assertSame('2026-01-05 05:15:00', DB::table('bookings')->where('id', $id)->value('created_at'));
    }
}
