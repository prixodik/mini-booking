# Design

## Context

Проверено запуском на временной SQLite-БД (Laravel 12.72, PHP 8.4), `app.timezone = UTC`, модель с `datetime`-cast:

| Что делаем | Результат |
|---|---|
| Записать `Carbon` в поясе Москвы (`09:00 MSK`) как есть | В БД лежит `09:00:00`: Laravel форматирует момент **в его собственном поясе**, не переводя в UTC |
| Записать тот же момент через `->utc()` | В БД `06:00:00` — верно |
| Прочитать значение через cast | Carbon в UTC; `setTimezone('Europe/Moscow')` даёт `+03:00` верно |
| `where('starts_at', '>=', $moscowCarbon)` | Подставляется московская строка — сравнение с UTC-данными неверно |
| `where('starts_at', '>=', $moscowCarbon->copy()->utc())` | Верно |

Вывод: если везде работать в UTC (в том числе `now()`), всё просто работает; единственные места, где в код попадает не-UTC момент, —
границы «сегодня по времени расписания» и построение сетки слотов. Там перевод в UTC делается явно, в одном месте.

Текущий код: `Slot` с аксессорами `utcAttribute()` и `toDbValue()`, `SlotSeeder` и `SlotController` берут `config('app.timezone')`.

## Goals / Non-Goals

**Goals:** одно время в БД (UTC); естественный `where('starts_at', '>', now())` верен; смена пояса не раздваивает расписание; меньше
самописного кода в модели; формат API и frontend не меняются.

**Non-Goals:** миграция данных существующих БД; несколько комнат или поясов; перевод временных полей во frontend.

## Decisions

### 1. Конфигурация

`config/app.php`: `'timezone' => 'UTC'` с комментарием «В БД и в логах всегда UTC; пояс расписания — config('booking.timezone')».
Без `env()`: старый `APP_TIMEZONE=Europe/Moscow` в чьём-то `.env` иначе заставил бы cast читать UTC-строки как московские
(слоты сдвинулись бы на 3 часа без единой ошибки). `config/booking.php`:

```php
// Часовой пояс расписания: в нём считаются часы работы (09:00–18:00), «сегодня» и время для клиента.
// В БД всё хранится в UTC (config('app.timezone')).
'timezone' => env('BOOKING_TIMEZONE', 'Europe/Moscow'),
```

`.env.example`: `BOOKING_TIMEZONE=Europe/Moscow` (строка `APP_TIMEZONE` удаляется). `phpunit.xml`: `<env name="BOOKING_TIMEZONE" value="Europe/Moscow" force="true"/>` и
`<server ...>` (строки про `APP_TIMEZONE` удаляются: он больше не читается).

### 2. Модель `Slot`

```php
protected function casts(): array
{
    return ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
}

/** Слоты, начинающиеся не раньше $moment. В БД UTC, поэтому момент (в любом поясе) переводится в UTC. */
public function scopeStartingFrom(Builder $query, CarbonInterface $moment): Builder
{
    return $query->where('starts_at', '>=', $moment->copy()->utc());
}
```

Удаляются `DB_FORMAT`, `toDbValue()`, `startsAt()`, `endsAt()`, `utcAttribute()` и `use ...Attribute`. `isPast()` не меняется (сравнение моментов).
PHPDoc: «starts_at/ends_at хранятся в UTC. Не-UTC `Carbon` перед записью приводите через `->utc()` (Laravel форматирует момент в его собственном поясе)».
Пишет слоты только `SlotSeeder` (явно через `->utc()`).

### 3. Выдача в пояс расписания

`SlotResource`: приватный метод `local($moment)` → `$moment->copy()->setTimezone(config('booking.timezone'))->toIso8601String()` для `starts_at` и `ends_at`.
`SlotController`:

```php
$timezone = config('booking.timezone');
$slots = Slot::with('activeBooking')
    ->startingFrom(now($timezone)->startOfDay())   // начало сегодняшнего дня по времени расписания
    ->orderBy('starts_at')->get();
// meta: 'timezone' => $timezone, 'server_time' => now($timezone)->toIso8601String()
```

### 4. Сидер

```php
$timezone = config('booking.timezone');
$this->assertScheduleMatchesTimezone($timezone, $openHour, $closeHour, $minutes);

$today = now($timezone)->startOfDay();
// ... цикл как сейчас; в строках:
'starts_at' => $start->copy()->utc()->toDateTimeString(),
'ends_at' => $end->copy()->utc()->toDateTimeString(),
'created_at' => $nowUtc, 'updated_at' => $nowUtc,   // $nowUtc = now()->toDateTimeString() (app.timezone = UTC)
```

`insertOrIgnore` обходит cast, поэтому перевод в UTC здесь явный.

### 5. Защита D

```php
/** Слот должен лежать в сетке расписания (open_hour..close_hour, шаг slot_minutes) в ТЕКУЩЕМ поясе, иначе расписание раздвоится. */
private function assertScheduleMatchesTimezone(string $timezone, int $open, int $close, int $minutes): void
{
    foreach (Slot::query()->pluck('starts_at') as $startsAt) {
        $local = $startsAt->copy()->setTimezone($timezone);
        $minuteOfDay = $local->hour * 60 + $local->minute;
        $fits = $minuteOfDay >= $open * 60 && $minuteOfDay < $close * 60 && ($minuteOfDay - $open * 60) % $minutes === 0;

        if (! $fits) {
            throw new RuntimeException(
                "В БД уже есть слот {$local->toDateTimeString()} ({$timezone}), который не попадает в сетку {$open}:00–{$close}:00. "
                .'Вероятно, сменился BOOKING_TIMEZONE или часы работы. Чтобы пересоздать расписание, выполните: php artisan migrate:fresh --seed'
            );
        }
    }
}
```

Почему это ловит смену пояса: сдвиг на любое ненулевое число часов выводит крайние слоты за окно 09:00–18:00; сдвиг не на целое число часов даёт минуты ≠ 0.
Смена на пояс с тем же смещением (Москва → Минск) ничего не ломает и проходит. Проверка выполняется по всем слотам (63 строки в демо; при росте таблицы — пройтись по сегодняшним и будущим слотам).

### 6. Тесты

В `Tests\TestCase`: `freezeTime(string $local)` — `Carbon::setTestNow(Carbon::parse($local, config('booking.timezone'))->utc())` (время в тестах в UTC, как в приложении:
иначе `now()` вернёт Carbon в московском поясе и ошибётся при подстановке в запрос) и `slotAt(string $local): Slot`; `tearDown` сбрасывает `setTestNow`.
Существующие тесты переписываются на эти помощники (содержимое проверок не меняется). Новые:

1. `test_natural_where_query_on_starts_at_is_correct`: при «09:30» `Slot::where('starts_at', '>', now())->count()` = 62 (из 63 слотов не входит только слот 09:00) — закрывает И-5.
2. `test_slots_and_timestamps_are_stored_in_utc`: слот 09:00 Москвы лежит в БД как `06:00:00`; `bookings.created_at` брони, созданной в 08:15 Москвы, — `05:15:00` — закрывает Ж-9.
3. `test_seeder_refuses_schedule_after_timezone_change`: засеять в Москве, `config(['booking.timezone' => 'Asia/Novosibirsk'])`, сидер бросает `RuntimeException`, слотов по-прежнему 63 — закрывает И-3.

### 7. Документы

README: таблица настроек (`BOOKING_TIMEZONE`, `APP_TIMEZONE` не используется), абзац про смену пояса (команда `migrate:fresh --seed`, сидер откажется), раздел «Каким временем оперирует приложение»
(всё в UTC; пояс расписания; формат API; как это соотносится с ТЗ), абзац про тесты. `openspec/config.yaml` (строка про `app.timezone`) и `openspec/traceability.md` (пункт 2 и допущение 2) — формулировки.
Прежние `design.md` непринятых к архивации changes остаются историей; требования определяют дельты этого change.

## Alternatives considered

- **Всё по-московски (`app.timezone = Europe/Moscow`, слоты без перевода).** Меньше кода, но хранить местное время слабее (переходы на летнее время в других поясах), а смена пояса на заполненной базе сдвигает все слоты.
- **Оставить как есть и описать в README.** Не убирает ловушку И-5 и смешение поясов.
- **Setter в `Slot`, приводящий запись к UTC.** Закрыл бы ловушку записи, но вернул бы самописный код в модель; писать слоты может только сидер (см. Open Questions в `proposal.md`).

## Risks / Trade-offs

- **ТЗ:** «время считается в часовом поясе, указанном в конфигурации Laravel». Пояс расписания лежит в конфигурации Laravel (`config/booking.php`), `app.timezone` — UTC. Фраза для защиты и строка README есть; риск — формальная трактовка «именно `app.timezone`».
- Существующие локальные БД: слоты (UTC) остаются корректными, но `created_at`/`cancelled_at` были московскими; для демо достаточно `php artisan migrate:fresh --seed`.
- Защита D выбрасывает исключение, а не просто сообщение: код возврата `db:seed` ненулевой, это намеренно (сигнал для CI и скриптов).

## Migration Plan

Backend и README; миграции не меняются. Локальный `backend/.env`: заменить `APP_TIMEZONE=...` на `BOOKING_TIMEZONE=Europe/Moscow` (старый `APP_TIMEZONE` теперь игнорируется), затем `php artisan migrate:fresh --seed`.
Откат — revert коммитов.
