# Proposal

## Why

ТЗ п. 2: нужны миграции слотов и бронирований и идемпотентный Seeder расписания. От структуры таблиц зависят
гарантия «одно активное бронирование на слот» (п. 3) и корректность времени.

## What Changes

- Миграции `slots` и `bookings` (отмена хранится как «мягкая»: `cancelled_at`, история сохраняется).
- Уникальность активной брони обеспечивается индексом БД (`bookings.active_slot_id` UNIQUE, NULL у отменённых).
- Модели `Slot`, `Booking`; время хранится в UTC, расписание вычисляется в `app.timezone`.
- `config/booking.php`: час открытия 9, час закрытия 18, длительность слота 60 мин, горизонт 7 дней.
- `SlotSeeder` (вызывается из `DatabaseSeeder`): 7 календарных дней × 9 слотов, идемпотентный.

Вне объёма: Scheduler/cron для продления расписания (п. 1 ТЗ).

## Capabilities

### New Capabilities
- `schedule`: слоты, часовой пояс расписания, определение «прошедшего» слота, Seeder.
- `booking-integrity`: структура бронирований, мягкая отмена и гарантия одного активного бронирования на слот на уровне БД.

### Modified Capabilities

## Impact

`backend/database/migrations/*`, `backend/database/seeders/*`, `backend/app/Models/{Slot,Booking}.php`, `backend/config/booking.php`.
