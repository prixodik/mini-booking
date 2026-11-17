# Proposal

## Why

ТЗ п. 3: фронтенду нужен `GET /api/slots` со слотами, их статусом и ID активной брони, без персональных данных.

## What Changes

- `GET /api/slots`: все слоты по возрастанию `starts_at`, время с UTC-смещением пояса расписания, `status`, `booking_id`.
- В `meta` отдаются `timezone` (IANA-имя пояса расписания) и `server_time`, чтобы frontend показывал время в поясе расписания.
- Имя и email клиентов в ответ не попадают.

Вне объёма: пагинация, фильтры по дате на сервере (фильтрация на клиенте разрешена п. 4 ТЗ).

## Capabilities

### New Capabilities
- `slots-api`: контракт и поведение `GET /api/slots`.

### Modified Capabilities

## Impact

`backend/routes/api.php`, `SlotController`, `SlotResource`. Зависит от `add-data-model-and-seeding`.
