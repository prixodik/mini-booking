# Spec Delta

## MODIFIED Requirements

### Requirement: Schedule timezone
Расписание SHALL вычисляться в часовом поясе `config('booking.timezone')` (`BOOKING_TIMEZONE`, по умолчанию `Europe/Moscow`). Часовой пояс приложения `config('app.timezone')` MUST быть зафиксирован значением `UTC`, и все даты и время в БД (`starts_at`, `ends_at`, `created_at`, `updated_at`, `cancelled_at`) MUST храниться в UTC. Слоты MUST быть однозначными моментами времени, поэтому обычное сравнение `where('starts_at', '>', now())` MUST давать верный результат без ручного перевода пояса.

#### Scenario: Слот 09:00 по времени расписания
- **WHEN** `booking.timezone` равен `Europe/Moscow` и Seeder создал слот на 09:00 локального времени
- **THEN** в БД он сохранён как `06:00:00` (UTC), а в API отдаётся как `…T09:00:00+03:00`

#### Scenario: Служебные поля в UTC
- **WHEN** бронь создана в 08:15 по времени расписания (Москва)
- **THEN** `bookings.created_at` в БД равен `05:15:00` (UTC), то есть в том же поясе, что и `slots.starts_at`

#### Scenario: Естественный запрос по времени
- **WHEN** сейчас 09:30 по времени расписания и выполняется `Slot::where('starts_at', '>', now())`
- **THEN** результат содержит все слоты, которые начинаются после 09:30 (слот 09:00 в него не входит), без дополнительных преобразований пояса

### Requirement: Seeder creates seven calendar days
`SlotSeeder` MUST создавать слоты на 7 календарных дней, считая сегодняшний день (сегодня … сегодня+6 по `booking.timezone`), по 9 слотов в день, то есть 63 слота. Слоты сегодняшнего дня, которые начались ранее текущего момента, MUST создаваться по общему расписанию.

#### Scenario: Первый запуск
- **WHEN** Seeder запускается на пустой БД
- **THEN** создаётся 63 слота, включая все 9 слотов сегодняшнего дня, даже если часть из них уже прошла

## ADDED Requirements

### Requirement: Seeder refuses a schedule created in another timezone
Перед добавлением слотов `SlotSeeder` MUST проверить уже созданные слоты: каждый из них должен попадать в сетку расписания (час начала от `open_hour` до `close_hour` с шагом `slot_minutes`, считая по `booking.timezone`). Если хотя бы один слот в сетку не попадает (сменили `BOOKING_TIMEZONE` или часы работы), Seeder MUST NOT добавлять слоты и MUST завершиться ошибкой с понятным сообщением и указанием выполнить `php artisan migrate:fresh --seed`; данные в БД остаются без изменений.

#### Scenario: Смена пояса на заполненной базе
- **WHEN** БД засеяна при `BOOKING_TIMEZONE=Europe/Moscow`, затем задан `Asia/Novosibirsk` и снова запущен Seeder
- **THEN** Seeder завершается ошибкой, число слотов остаётся 63, дубликатов и второй сетки нет

#### Scenario: Тот же пояс
- **WHEN** Seeder запускается повторно при неизменном `booking.timezone`
- **THEN** проверка проходит, поведение «идемпотентность» и «расширение расписания» не меняется

#### Scenario: Пустая база
- **WHEN** Seeder запускается на пустой БД при любом `booking.timezone`
- **THEN** проверка не мешает: создаётся 63 слота
