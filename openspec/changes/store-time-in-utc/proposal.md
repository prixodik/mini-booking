# Proposal

## Why

Ревью (`review-mini-booking.md`, И-3, И-5, Ж-9) и разбор `timezones-explained.md`: время в проекте хранится в двух поясах сразу.
`slots.starts_at`/`ends_at` лежат в БД в UTC (самописные аксессоры в `Slot`), а `created_at`, `updated_at`, `cancelled_at` — в
поясе приложения (`APP_TIMEZONE=Europe/Moscow`). Из этого вырастают три проблемы:

- **Ж-9.** Две колонки одной таблицы нельзя сравнить глазами: слот 09:00 Москвы лежит как `06:00`, а бронь, созданная в 08:15 Москвы, — как `08:15`.
- **И-5.** Естественный запрос `Slot::where('starts_at', '>', now())` подставляет московское время и ошибается на 3 часа; правильно писать через `Slot::toDbValue(...)`, и об этом надо помнить.
- **И-3.** Смена пояса на заполненной базе и повторный `db:seed` создают вторую сетку слотов (проверено: 63 → 91, в дне 13 слотов, «09:00–22:00»), а README обещает обратное.

Alex выбрал вариант **B + D**: всё в базе в UTC, пояс расписания — отдельная настройка, сидер защищён от раздвоения расписания.

## What Changes

**B. Всё в UTC, пояс расписания — отдельная настройка.**
- `config/app.php`: `timezone` зафиксирован значением `UTC` (без `env('APP_TIMEZONE')`), поэтому `now()`, `created_at`, `cancelled_at` и
  все остальные даты в БД — UTC, и старый `APP_TIMEZONE=Europe/Moscow` в чьём-то `.env` не может молча сдвинуть данные.
- `config/booking.php`: новый ключ `timezone` (`BOOKING_TIMEZONE`, по умолчанию `Europe/Moscow`) — пояс, в котором считаются часы
  работы 09:00–18:00, определяются «сегодня» и показывается время клиенту.
- `Slot`: убираются самописные аксессоры и `Slot::toDbValue()`/`DB_FORMAT`; `starts_at`/`ends_at` используют `datetime`-cast и приводятся к UTC при записи через модель.
  Scope `startingFrom()` остаётся (нужен для «с начала сегодняшнего дня по времени расписания»), но переводит границу в UTC через `->utc()`.
- `SlotResource`, `SlotController` (`meta.timezone`, `server_time`, начало «сегодня»), `SlotSeeder`, `SlotFactory` берут пояс из `config('booking.timezone')`.
  Формат API не меняется: `2026-11-16T09:00:00+03:00`, `meta.timezone = Europe/Moscow`, frontend не затрагивается.
- `.env.example`: `BOOKING_TIMEZONE=Europe/Moscow` вместо `APP_TIMEZONE=Europe/Moscow`; `phpunit.xml` принудительно задаёт `BOOKING_TIMEZONE`.

**D. Защита сидера от раздвоения расписания.**
- Перед вставкой `SlotSeeder` проверяет уже созданные слоты: каждый должен попадать в сетку `open_hour…close_hour` с шагом
  `slot_minutes` **в текущем поясе расписания**. Если нет (сменили `BOOKING_TIMEZONE` или часы работы), сидер ничего не добавляет и
  завершается ошибкой с понятным текстом и подсказкой `php artisan migrate:fresh --seed`.

**Тесты и README.** Тесты переводятся на пояс расписания через общие помощники в `TestCase`; добавляются тесты: «естественный запрос
`where('starts_at', '>', now())` даёт верный результат» (закрывает И-5), «в БД слоты лежат в UTC», «сидер отказывается добавлять слоты после
смены пояса». README: настройки `.env`, раздел «Каким временем оперирует приложение», строка про трактовку ТЗ («пояс из конфигурации Laravel»), замена обещания про смену пояса.

Вне объёма: перенос данных существующих БД (для демо — `migrate:fresh --seed`), поддержка нескольких комнат или поясов, изменения frontend и API-контракта.

## Capabilities

### New Capabilities

### Modified Capabilities
- `schedule`: пояс расписания — `config('booking.timezone')`; все даты в БД в UTC; сидер отказывается добавлять слоты в расписание другого пояса.
- `project-setup`: `.env.example` задаёт `BOOKING_TIMEZONE`; `app.timezone` зафиксирован в UTC.
- `slots-api`: `meta.timezone` и время слотов — в поясе расписания `booking.timezone`.
- `backend-tests`: результат не зависит от `BOOKING_TIMEZONE` в окружении (принудительно в `phpunit.xml`).
- `project-readme`: описание настройки пояса, хранения времени и действий при смене пояса.

## Impact

`backend/config/app.php`, `config/booking.php`, `.env.example`, `phpunit.xml`, `app/Models/Slot.php`, `app/Http/Resources/SlotResource.php`,
`app/Http/Controllers/SlotController.php`, `database/seeders/SlotSeeder.php`, `database/factories/SlotFactory.php`,
`tests/TestCase.php` и все три файла тестов, `README.md`, `openspec/config.yaml` и `openspec/traceability.md` (формулировки про `app.timezone`).
Frontend и формат API не меняются. Миграции не меняются.

Порядок: реализуется после `slots-from-today` и `single-source-active-booking`, на которые опирается (меняет их код). Архивировать после
`add-data-model-and-seeding`, `scaffold-project`, `add-slots-api`, `add-tests-and-readme` (дельты `MODIFIED`); дельта `List slots` включает
формулировку «с начала сегодняшнего дня» из `slots-from-today` и применяется после него.

## Decisions of Alex

- Вариант B + D из `timezones-explained.md` (17.11.2026).

## Решения по реализации

- `app.timezone` зафиксирован в UTC.
- Модель `Slot` нормализует время при записи. При пакетной вставке сидер переводит даты в UTC явно.
