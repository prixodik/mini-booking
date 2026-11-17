# Design

## Context

- `Slot::activeBooking()`: `hasOne(Booking::class)->whereNull('cancelled_at')` — ключ `slot_id`.
- UNIQUE-индекс — на `bookings.active_slot_id`; `BookingService` при создании пишет `active_slot_id = slot_id`, при отмене одним
  `UPDATE` ставит `cancelled_at` и обнуляет `active_slot_id`.
- `BookingFactory::definition()` не задаёт `active_slot_id`; `forSlot()` задаёт и `slot_id`, и `active_slot_id`.

## Decisions

1. Связь по `active_slot_id`:
   ```php
   /** Активная бронь: у неё active_slot_id = id слота; UNIQUE-индекс гарантирует, что такая одна. */
   public function activeBooking(): HasOne
   {
       return $this->hasOne(Booking::class, 'active_slot_id');
   }
   ```
   Eager loading (`Slot::with('activeBooking')`) превращается в `WHERE active_slot_id IN (...)` по уникальному индексу.
   `SlotController`, `SlotResource` и `BookingService` не меняются.
2. Фабрика:
   ```php
   public function definition(): array
   {
       return [
           'slot_id' => Slot::factory(),
           // Активная бронь: active_slot_id = slot_id (так её создаёт BookingService).
           'active_slot_id' => fn (array $attributes) => $attributes['slot_id'],
           'customer_name' => fake()->name(),
           'customer_email' => fake()->safeEmail(),
           'cancelled_at' => null,
       ];
   }

   public function forSlot(Slot $slot): static
   {
       return $this->state(fn () => ['slot_id' => $slot->id]);
   }

   public function cancelled(): static
   {
       return $this->state(fn () => ['cancelled_at' => now(), 'active_slot_id' => null]);
   }
   ```
   Laravel сначала превращает вложенную фабрику `Slot::factory()` в ID, затем вызывает замыкания с уже готовыми атрибутами, поэтому
   `$attributes['slot_id']` — число. `active_slot_id` из `forSlot()` уходит: его выставляет замыкание из `definition()`.
3. Существующие проверки в тестах `Booking::where('slot_id', …)->whereNull('cancelled_at')->count()` остаются: они проверяют
   результат с другой стороны и при согласованных полях дают то же число.

Рассмотрено: CHECK-ограничение в миграции, чтобы БД сама запрещала рассогласование. Надёжнее, но в Laravel это сырой SQL
(`DB::statement`) с разным синтаксисом изменения таблиц для SQLite и MySQL; для тестового с правкой на защите — лишнее.
Можно упомянуть на защите как следующий шаг.

## Risks / Trade-offs

- Если кто-то обновит `cancelled_at` в обход `BookingService`, не обнулив `active_slot_id`, API покажет бронь активной — так же,
  как индекс будет считать слот занятым; источник правды один, рассогласования между API и гарантией больше нет.

## Migration Plan

Только код и тесты; схема БД не меняется. Архивировать после `add-data-model-and-seeding` (дельта `MODIFIED`).
