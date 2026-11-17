# Proposal

## Why

Ревью (`review-mini-booking.md`, И-2): `DELETE /api/bookings/99999999999999999999` отвечает 500 со стектрейсом. Маршрут
ограничен `whereNumber('id')`, поэтому число вне диапазона `int` доходит до `BookingController::destroy(int $id)`, и PHP бросает
`TypeError`. При `APP_DEBUG=true` в ответе видны пути к файлам. ТЗ, п. 3 ожидает предсказуемые коды ответов API; такой брони быть не
может, правильный ответ — 404 «Бронь не найдена». Alex попросил исправить.

## What Changes

- `BookingController::destroy` принимает ID строкой и проверяет его `filter_var(..., FILTER_VALIDATE_INT)`. Если число не помещается
  в `int`, API отвечает 404 «Бронь не найдена» (`BookingNotFoundException::unknown()`), как для любого несуществующего ID.
- Новый тест на этот случай в `tests/Feature/BookingApiTest.php`.

Вне объёма: остальные пункты ревью (в т. ч. Ж-3 — `slot_id: true` в POST), изменения frontend.

## Capabilities

### New Capabilities

### Modified Capabilities
- `bookings-api`: ID вне диапазона целых чисел при отмене — 404 «Бронь не найдена», а не 500.

## Impact

`backend/app/Http/Controllers/BookingController.php`, `backend/tests/Feature/BookingApiTest.php`. Frontend, БД и формат ответов не меняются.

## Decisions of Alex

- Исправить И-2 из отчёта ревью (17.11.2026).

## Open Questions

Нет: нужно только одобрение change (выбор способа — в `design.md`).
