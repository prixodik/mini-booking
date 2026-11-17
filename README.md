# Mini Booking

Бронирование одной переговорной комнаты по часовым слотам (09:00–18:00, каждый день, включая выходные).
Backend — Laravel 12 (JSON API), frontend — Nuxt 3 (SPA) + PrimeVue 4, БД — SQLite.

## Оформление интерфейса

- Верстка по БЭМ (`block__element--modifier`, один компонент — один блок), стили в `<style scoped>` компонентов, общие токены (цвета, отступы, радиусы, тени, шрифт) — `frontend/assets/css/tokens.css`.
- Тема PrimeVue — Aura, только светлая, фиолетовый акцент `#6D28D9`, настраивается в `frontend/plugins/primevue.js` (`definePreset`), внутренние классы `.p-*` не переопределяются.
- Шрифт Nunito Sans (`@fontsource/nunito-sans`) подключается локально; `@primeuix/themes` закреплён на MIT-версии 2.0.3.
- Правила и расчёт контраста: `openspec/changes/redesign-booking-ui/design.md`.

## Структура репозитория

```
backend/     Laravel 12: API, миграции, Seeder, тесты
frontend/    Nuxt 3 (SPA): pages/, layouts/, components/booking/, composables/, services/, utils/, plugins/
openspec/    спецификации и changes (разработка по принципу spec first)
AGENTS.md    правило «spec first» и порядок changes
```

## Требования

- PHP 8.2+ (с расширениями `pdo_sqlite`, `mbstring`, `xml`, `curl`), Composer 2. Если в `PATH` несколько версий PHP, убедитесь, что `php -v` показывает 8.2+ (Composer и `artisan` берут `php` из `PATH`)
- Node.js 20.19+ или 22.12+ и npm (для frontend; OpenSpec требует Node.js 22.12+)

## Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite        # PowerShell: New-Item database/database.sqlite -ItemType File
php artisan migrate                   # миграции: slots, bookings
php artisan db:seed                   # расписание на 7 дней × 9 слотов (можно запускать повторно)
php artisan serve                     # http://localhost:8000
```

### Если установка идёт долго или падает

- **`composer install` долго стоит и печатает сотни предупреждений «Could not authenticate against github.com».**
  Composer не может скачать архивы пакетов с GitHub из-за анонимного лимита запросов и ставит пакеты из исходников (`git clone`):
  это работает, но занимает минуты (у нас ~5 минут). Чтобы ускорить, создайте на GitHub персональный токен доступа (без прав на запись)
  и выполните один раз `composer config -g github-oauth.github.com <ваш_токен>`. Токен хранится только в вашей конфигурации Composer, в репозиторий его не добавляйте.
- **Ошибка «Filename too long» на Windows** (например, при установке `league/commonmark`) возникает при длинном пути к проекту.
  Клонируйте репозиторий в короткий путь (например, `D:\mb`) или выполните `git config --global core.longpaths true`.

### Настройки `.env`

| Переменная | Значение по умолчанию | Назначение |
|---|---|---|
| `APP_TIMEZONE` | `Europe/Moscow` | часовой пояс расписания (читается в `config/app.php`) |
| `APP_LOCALE` | `ru` | язык сообщений |
| `DB_CONNECTION` | `sqlite` | БД; файл `backend/database/database.sqlite` |

Параметры расписания (час открытия, закрытия, длительность слота, число дней) — в `backend/config/booking.php`.
Чтобы сменить часовой пояс, измените `APP_TIMEZONE` и снова запустите `php artisan db:seed`:
добавятся недостающие слоты, уже созданные слоты и бронирования не меняются.

### Тесты

```bash
cd backend
php artisan test
```

Тесты идут на SQLite in-memory и не трогают рабочую БД. Время в тестах фиксируется (`Carbon::setTestNow`),
поэтому результат не зависит от времени суток, а часовой пояс `Europe/Moscow` задан в `phpunit.xml` принудительно,
поэтому `APP_TIMEZONE` в `.env` или в окружении на тесты не влияет. Два обязательных теста — в `tests/Feature/BookingApiTest.php`
(`test_books_free_future_slot`, `test_second_request_for_taken_slot_returns_409_and_creates_no_booking`),
остальные — дополнительные (валидация, прошедший слот, отмена, Seeder, список слотов).

## Frontend

```bash
cd frontend
npm install
npm run dev                           # http://localhost:3000
```

В режиме разработки запросы `/api/*` проксируются на `http://localhost:8000/api` (Nitro `devProxy`, см. `nuxt.config.js`),
поэтому CORS настраивать не нужно. Другой адрес backend: `NUXT_API_PROXY_TARGET=http://host:port/api npm run dev`.
Сначала запустите backend, затем frontend.

## API

| Метод | Путь | Ответ |
|---|---|---|
| GET | `/api/slots` | 200: `{"data": [{id, starts_at, ends_at, status, booking_id}], "meta": {timezone, server_time}}`; слоты с 00:00 сегодняшнего дня по времени расписания |
| POST | `/api/bookings` | 201 `{"data": {"id"}}`; 422 ошибки по полям; 409 слот занят или прошёл |
| DELETE | `/api/bookings/{id}` | 204; 404 `Бронь не найдена` / `Бронь уже отменена` |

`status` слота: `available`, `booked`, `past`. Имя и email клиента в список слотов не попадают.
Тело POST (JSON): `slot_id` — целое число, `customer_name`, `customer_email` — email с точкой в домене (то же правило формата, что в форме).

## Как обеспечена уникальность бронирования

Гарантию «у слота не больше одного активного бронирования» даёт база данных, а не проверка в коде:

- В `bookings` есть поле `active_slot_id` с **UNIQUE-индексом** (миграция `2026_01_01_000002_create_bookings_table.php`).
  У активной брони оно равно `slot_id`, у отменённой — `NULL`. UNIQUE-индекс допускает сколько угодно `NULL`
  (в SQLite, MySQL и PostgreSQL), но не допускает двух активных броней одного слота.
- `BookingService::create` просто вставляет запись и перехватывает `UniqueConstraintViolationException`,
  превращая её в ответ 409. Предварительной проверки «свободен ли слот» нет: между проверкой и вставкой
  возможна гонка, а индекс срабатывает атомарно. Поэтому при одновременных запросах ровно один получит 201, остальные — 409.
- Отмена «мягкая»: `cancelled_at` заполняется, `active_slot_id` обнуляется, строка остаётся (история сохраняется).
  Отмена — атомарный `UPDATE … WHERE cancelled_at IS NULL`, поэтому из двух одновременных отмен успешна одна.
- `active_slot_id` ведётся только в `BookingService` (создание и отмена). По этому же полю API определяет активную бронь слота (`Slot::activeBooking()`), поэтому статус `booked` и гарантия БД опираются на один источник.

Ограничение: SQLite сериализует записи, поэтому настоящую параллельную гонку PHPUnit в одном процессе не воспроизвести;
тест проверяет сам инвариант (409 при повторном запросе и отказ БД на вторую активную бронь).

## Каким временем оперирует приложение

- Расписание строится в часовом поясе `config('app.timezone')` (`APP_TIMEZONE`, по умолчанию `Europe/Moscow`):
  слоты 09:00–10:00 … 17:00–18:00 по этому времени.
- В БД моменты слотов хранятся в **UTC** (`App\Models\Slot` переводит при записи и чтении), поэтому смена `APP_TIMEZONE`
  не портит сохранённые слоты. Поля `created_at`/`updated_at`/`cancelled_at` — служебные, пишутся в часовом поясе приложения.
- API отдаёт `starts_at`/`ends_at` в ISO 8601 с UTC-смещением пояса расписания (например `2026-11-16T09:00:00+03:00`)
  и имя пояса в `meta.timezone`. Frontend показывает даты и время именно в этом поясе, а не в поясе браузера.
- Слот считается **прошедшим**, если `starts_at <= now()` серверного времени; статус `past` приоритетнее `booked`.

## Допущения и что не сделано

- Авторизация, управление комнатами, админка, Scheduler/cron для продления расписания — вне объёма ТЗ.
- **Безопасность демо (осознанно, ТЗ п. 3: отмена по ID без авторизации).** `booking_id` виден в `GET /api/slots`, а ID идут
  по порядку, поэтому любой посетитель может отменить чужую бронь. В реальном проекте: возвращать при создании секретный токен
  отмены (или UUID) и требовать его в `DELETE`, плюс авторизация. На `POST /api/bookings` нет ограничения частоты — один клиент
  может занять все слоты; в реальном проекте — `->middleware('throttle:10,1')` на маршруте.
- Слоты текущего дня, которые уже начались, создаются Seeder'ом и отображаются как «Прошёл».
- Бронь уже начавшегося слота API отменить позволяет (слот остаётся «Прошёл»), в интерфейсе кнопка отмены для таких слотов скрыта.
- `GET /api/slots` отдаёт слоты с начала сегодняшнего дня (по времени расписания), без пагинации; слоты прошлых дней остаются в БД, но в ответ не попадают.
- Автообновление списка по таймеру нет: список обновляется после действий и при конфликте (409).
- Production-сборка frontend (`npm run build`) требует reverse-proxy для `/api` — в задании не настраивалась.
- Тесты frontend не писались (ТЗ их не требует).

## Разработка через OpenSpec

Проект разрабатывается по принципу spec first: сначала change в `openspec/changes/`, потом код. OpenSpec 2.x требует Node.js 22.12+.

```bash
npx -y @fission-ai/openspec@2.0.0 list             # активные changes
npx -y @fission-ai/openspec@2.0.0 validate --all   # проверка спецификаций
```

Порядок changes: `scaffold-project` → `add-data-model-and-seeding` → `add-slots-api` → `add-bookings-api` →
`add-booking-frontend` → `add-tests-and-readme`. Соответствие ТЗ и спецификаций — `openspec/traceability.md`.
