# Tasks

## 1. Конфигурация и модели

- [ ] 1.1 `config/booking.php`: `open_hour=9`, `close_hour=18`, `slot_minutes=60`, `days_ahead=7`
- [ ] 1.2 Миграция `slots` (`starts_at` UNIQUE, `ends_at`)
- [ ] 1.3 Миграция `bookings` (FK `slot_id`, `customer_name`, `customer_email`, `active_slot_id` UNIQUE NULL, `cancelled_at` NULL)
- [ ] 1.4 Модели `Slot` и `Booking`; хранение времени в UTC, вывод в `app.timezone`; связь `Slot::activeBooking`
- [ ] 1.5 Фабрики `Slot` и `Booking` (для тестов)

## 2. Seeder

- [ ] 2.1 `SlotSeeder`: 7 дней × 9 слотов от начала сегодняшнего дня по `app.timezone`, идемпотентная вставка
- [ ] 2.2 Подключить в `DatabaseSeeder`; проверить `php artisan migrate:fresh --seed`, затем повторный `db:seed` → 63 слота
- [ ] 2.3 Проверить вручную: запуск с замороженным «завтрашним» временем добавляет 9 слотов, старые не трогаются
