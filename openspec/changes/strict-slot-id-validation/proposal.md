# Proposal

## Why

Ревью (`review-mini-booking.md`, Ж-3): `POST /api/bookings` с телом `{"slot_id": true, ...}` проходит правило `integer`
(`filter_var(true, FILTER_VALIDATE_INT)` даёт `1`), и бронируется слот с ID 1. ТЗ, п. 3: «Используйте валидацию Laravel…
Для неверных полей… верните 422 с ошибками по полям». Логическое значение — неверное поле. Alex попросил исправить.

## What Changes

- `StoreBookingRequest`: правило `slot_id` — `integer:strict` вместо `integer`: принимается только JSON-число, а `true`, `false`,
  `1.5` и строки (`"5"`) получают 422 с ошибкой в `errors.slot_id`.
- Тест на `slot_id: true` и `slot_id: "5"`.

Вне объёма: правила для имени и email (в т. ч. Ж-4 — расхождение проверки email), остальные пункты ревью, frontend.

## Capabilities

### New Capabilities

### Modified Capabilities
- `bookings-api`: `slot_id` должен быть целым числом JSON, а не логическим значением или строкой.

## Impact

`backend/app/Http/Requests/StoreBookingRequest.php`, `backend/tests/Feature/BookingApiTest.php`. Frontend уже отправляет
`slot_id` числом (`services/bookingApi.js`, JSON), поэтому не меняется. README: строка в таблице «API».

## Decisions of Alex

- Исправить Ж-3 из отчёта ревью (17.11.2026).

## Open Questions

Нет, если устраивает поведение для строк: `"slot_id": "5"` после правки получает 422 (см. `design.md`).
