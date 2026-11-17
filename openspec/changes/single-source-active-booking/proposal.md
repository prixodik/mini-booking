# Proposal

## Why

Ревью (`review-mini-booking.md`, Ж-1): «активность» брони определяется двумя полями. Гарантию «одна активная бронь на слот» даёт
UNIQUE-индекс на `bookings.active_slot_id`, а связь `Slot::activeBooking()`, по которой API считает статус `booked` и отдаёт
`booking_id`, смотрит на `cancelled_at IS NULL`. Сейчас поля согласованы, потому что их меняет только `BookingService`, но
`BookingFactory` без `forSlot()` создаёт бронь с `cancelled_at = NULL` и `active_slot_id = NULL`: для API она активная, для индекса —
нет. Кроме того, запрос связи идёт по `slot_id`, на котором в SQLite нет индекса, а по `active_slot_id` индекс есть. Alex попросил исправить.

## What Changes

- `Slot::activeBooking()` — `hasOne(Booking::class, 'active_slot_id')`: активная бронь определяется тем же полем, что и гарантия БД.
- `BookingFactory`: по умолчанию активная бронь согласована (`active_slot_id` = `slot_id`); новое состояние `cancelled()`
  (`cancelled_at` = сейчас, `active_slot_id` = NULL); `forSlot()` задаёт только слот.
- Тест: слот с бронью из фабрики по умолчанию — `booked`, с отменённой — `available`.

Вне объёма: CHECK-ограничение `(cancelled_at IS NULL) = (active_slot_id IS NOT NULL)` в БД (сырой SQL в миграции, сложнее объяснить
на защите — см. `design.md`), остальные пункты ревью, изменения API.

## Capabilities

### New Capabilities

### Modified Capabilities
- `booking-integrity`: связь активной брони определяется по `active_slot_id`.

## Impact

`backend/app/Models/Slot.php`, `backend/database/factories/BookingFactory.php`, `backend/tests/Feature/SlotsApiTest.php`.
Формат API, миграции и frontend не меняются.

## Decisions of Alex

- Исправить Ж-1 из отчёта ревью (17.11.2026).

## Open Questions

Нет: нужно только одобрение change.
