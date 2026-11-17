<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Slot;
use Database\Seeders\SlotSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Время фиксируем: утро рабочего дня, чтобы будущий свободный слот был при любом реальном времени запуска.
        $this->setScheduleNow('2026-01-05 08:00');
        $this->seed(SlotSeeder::class);
    }

    private function futureSlot(): Slot
    {
        // Сегодня 10:00 по времени расписания: сейчас 08:00, слот в будущем.
        return $this->slotAt('2026-01-05 10:00');
    }

    private function payload(Slot $slot, array $overrides = []): array
    {
        return array_merge([
            'slot_id' => $slot->id,
            'customer_name' => 'Иван Петров',
            'customer_email' => 'ivan@example.com',
        ], $overrides);
    }

    // --- Обязательные тесты из ТЗ ---

    public function test_books_free_future_slot(): void
    {
        $slot = $this->futureSlot();

        $response = $this->postJson('/api/bookings', $this->payload($slot));

        $response->assertCreated()->assertJsonStructure(['data' => ['id']]);
        $this->assertDatabaseCount('bookings', 1);
        $this->assertDatabaseHas('bookings', [
            'id' => $response->json('data.id'),
            'slot_id' => $slot->id,
            'active_slot_id' => $slot->id,
            'cancelled_at' => null,
        ]);
    }

    public function test_second_request_for_taken_slot_returns_409_and_creates_no_booking(): void
    {
        $slot = $this->futureSlot();
        $this->postJson('/api/bookings', $this->payload($slot))->assertCreated();

        $this->postJson('/api/bookings', $this->payload($slot, ['customer_email' => 'other@example.com']))
            ->assertStatus(409)
            ->assertJsonStructure(['message']);

        $this->assertSame(1, Booking::where('slot_id', $slot->id)->whereNull('cancelled_at')->count());
        $this->assertDatabaseCount('bookings', 1);
    }

    // --- Дополнительные тесты ---

    public function test_booking_a_past_slot_returns_409(): void
    {
        $past = $this->futureSlot(); // сегодня 10:00
        $this->setScheduleNow('2026-01-05 10:30'); // слот уже начался

        $this->postJson('/api/bookings', $this->payload($past))->assertStatus(409);

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_validation_errors_are_returned_per_field_as_json(): void
    {
        // Без заголовка Accept ответ тоже должен быть JSON.
        $this->post('/api/bookings', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['slot_id', 'customer_name', 'customer_email']);

        $this->postJson('/api/bookings', ['slot_id' => 999999, 'customer_name' => 'A', 'customer_email' => 'a@b.co'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['slot_id'])
            ->assertJsonMissingValidationErrors(['customer_name', 'customer_email']);

        $this->postJson('/api/bookings', $this->payload($this->futureSlot(), ['customer_email' => 'not-an-email']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['customer_email']);
    }

    public function test_slot_id_must_be_a_json_integer(): void
    {
        $slot = $this->futureSlot();

        foreach ([true, (string) $slot->id] as $slotId) {
            $this->postJson('/api/bookings', $this->payload($slot, ['slot_id' => $slotId]))
                ->assertStatus(422)
                ->assertJsonValidationErrors(['slot_id']);
        }

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_email_format_matches_the_browser_check(): void
    {
        foreach (['a@b', 'a@localhost'] as $email) {
            $this->postJson('/api/bookings', $this->payload($this->futureSlot(), ['customer_email' => $email]))
                ->assertStatus(422)
                ->assertJsonValidationErrors(['customer_email']);
        }
        $this->assertDatabaseCount('bookings', 0);

        $this->postJson('/api/bookings', $this->payload($this->futureSlot(), ['customer_email' => 'иван@пример.рф']))
            ->assertCreated();
    }

    public function test_cancel_frees_the_slot_and_it_can_be_booked_again(): void
    {
        $slot = $this->futureSlot();
        $id = $this->postJson('/api/bookings', $this->payload($slot))->json('data.id');

        $this->deleteJson("/api/bookings/{$id}")->assertNoContent();

        $this->assertDatabaseHas('bookings', ['id' => $id, 'active_slot_id' => null]);
        $this->assertNotNull(Booking::find($id)->cancelled_at);

        $this->postJson('/api/bookings', $this->payload($slot))->assertCreated();
        $this->assertSame(1, Booking::where('slot_id', $slot->id)->whereNull('cancelled_at')->count());
        $this->assertDatabaseCount('bookings', 2); // отменённая бронь осталась в истории
    }

    public function test_repeated_cancel_and_unknown_id_return_404_with_different_messages(): void
    {
        $id = $this->postJson('/api/bookings', $this->payload($this->futureSlot()))->json('data.id');
        $this->deleteJson("/api/bookings/{$id}")->assertNoContent();

        $again = $this->deleteJson("/api/bookings/{$id}")->assertNotFound();
        $unknown = $this->deleteJson('/api/bookings/999999')->assertNotFound();

        $this->assertNotSame($again->json('message'), $unknown->json('message'));
    }

    public function test_cancel_with_out_of_range_id_returns_404(): void
    {
        // Число больше PHP_INT_MAX: раньше падало с TypeError (500).
        $this->deleteJson('/api/bookings/99999999999999999999')
            ->assertNotFound()
            ->assertExactJson(['message' => 'Бронь не найдена.']);
    }

    public function test_database_rejects_a_second_active_booking_for_the_same_slot(): void
    {
        $slot = $this->futureSlot();
        Booking::factory()->forSlot($slot)->create();

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        Booking::factory()->forSlot($slot)->create();
    }
}
