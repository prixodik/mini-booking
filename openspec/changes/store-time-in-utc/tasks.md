# Tasks

Решения согласованы с Alex (см. `proposal.md`).

## 1. Решения

- [x] 1.1 Зафиксировать `app.timezone` в UTC и приводить даты слота к UTC при записи через модель

## 2. Подготовка тестов (до правки кода)

- [x] 2.1 `Tests\TestCase`: помощники `setScheduleNow()` и `slotAt()`, сброс `setTestNow` в `tearDown`
- [x] 2.2 Тесты на прямой запрос по времени, хранение дат слота, создание и отмену брони в UTC и отказ сидера после смены пояса. До завершения реализации тесты записи через модель и смены пояса воспроизвели ошибки

## 3. Реализация

- [x] 3.1 `config/app.php`: `'timezone' => 'UTC'`; `config/booking.php`: `timezone`; `.env.example`: `BOOKING_TIMEZONE`; `phpunit.xml`: `BOOKING_TIMEZONE` вместо `APP_TIMEZONE`
- [x] 3.2 `Slot`: стандартный `datetime`-cast, `startingFrom()` с UTC-границей, перевод в UTC при записи в `fromDateTime()`
- [x] 3.3 `SlotResource`, `SlotController` (`meta`, начало «сегодня»), `SlotFactory`: пояс из `config('booking.timezone')`
- [x] 3.4 `SlotSeeder`: пояс из `booking.timezone`, явный перевод в UTC, защита `assertScheduleMatchesTimezone()`

## 4. Проверка и документы

- [x] 4.1 PHPUnit: 20 тестов прошли в обычном окружении и с `BOOKING_TIMEZONE=Asia/Tokyo` и `APP_TIMEZONE=America/Los_Angeles`
- [x] 4.2 Временная БД: повторный сид сохраняет 63 слота, после смены пояса сидер отказывает до записи, пересоздание даёт 63 слота. `GET /api/slots` через HTTP-ядро Laravel возвращает `+03:00` и `meta.timezone`
- [x] 4.3 README, `openspec/config.yaml`, `openspec/traceability.md` и локальный `backend/.env` обновлены
- [x] 4.4 Сохранение времени в браузере проверено владельцем проекта
- [x] 4.5 Локальная тестовая база пересоздана через `migrate:fresh --seed`. Проверены 63 слота в UTC, пустая таблица бронирований и ответ API в поясе расписания
- [x] 4.6 Строгая валидация OpenSpec прошла для всех 23 изменений. Использована согласованная версия 1.14.1, поскольку 2.0.0 недоступна в окружении
