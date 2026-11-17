# Соответствие ТЗ и спецификаций

Проверка покрытия пунктов ТЗ требованиями OpenSpec (`openspec/changes/*`). Пункты ТЗ — по его нумерации.

| Пункт ТЗ | Требование | Где в спецификации |
|---|---|---|
| 1. Одна комната, 09:00–18:00 каждый день, включая выходные; часовые слоты; один активный слот на бронь | 9 слотов в день, без исключения выходных; одна активная бронь | `schedule` (Daily slot grid), `booking-integrity` (One active booking per slot…) |
| 1. Вне объёма: авторизация, комнаты, админка, Scheduler/cron | Явно в Non-Goals / «Вне объёма» | `scaffold-project`, `add-data-model-and-seeding`, `add-bookings-api` (proposal) |
| 2. Миграции слотов и бронирований; структура и хранение отмен на выбор автора | `slots`, `bookings`, мягкая отмена `cancelled_at` | `add-data-model-and-seeding` (design, `booking-integrity`) |
| 2. Seeder: 7 дней включая сегодня × 9, часовой пояс Laravel, слоты начала дня как прошедшие | 63 слота в поясе `booking.timezone`, прошедшие создаются, даты в БД в UTC | `schedule` (Seeder creates seven calendar days, Schedule timezone) |
| 2. Повторный запуск без дубликатов; в другой день добавляет недостающее, старое сохраняет | Идемпотентность, расширение окна | `schedule` (Seeder is idempotent, Seeder extends schedule…) |
| 3. `GET /api/slots`: сортировка, ID, начало/конец с поясом, статус, ID активной брони, без имени/email | Поля, формат ISO 8601 со смещением, без ПДн | `slots-api` (List slots, Slot representation, No personal data) |
| 3. Прошедший слот: `starts_at <= now` | `past` при `starts_at <= now` | `schedule` (Past slot definition), `slots-api` (Slot status) |
| 3. `POST /api/bookings`: валидация Laravel, 201 + ID | FormRequest, `{"data":{"id"}}` | `bookings-api` (Create booking, Validation) |
| 3. 422 по полям, в т. ч. несуществующий `slot_id` | `errors.slot_id` | `bookings-api` (Validation, сценарий «Несуществующий slot_id») |
| 3. 409 для занятого или прошедшего слота с понятным сообщением | Два сообщения на русском | `bookings-api` (Occupied slot conflict, Past slot conflict) |
| 3. Параллельные запросы → ровно одна активная бронь, гарантия на backend через БД | UNIQUE `active_slot_id`, перехват нарушения → 409 | `booking-integrity`, `bookings-api` (Concurrent requests…) |
| 3. `DELETE /api/bookings/{id}`: 204, слот снова доступен, повтор — 204 или 404, без авторизации | 204; неизвестный ID — 404; повторная отмена — 404 с другим сообщением | `bookings-api` (Cancel booking, Rebooking after cancellation) |
| 4. Composition API, `<script setup>`, готовая UI-библиотека, удобство на ноутбуке и телефоне | PrimeVue 4, адаптивная вёрстка от 360 px | `project-setup` (Supported stack versions), `booking-ui` (Responsive layout) |
| 4. Дата, время, статус; различимость свободных/занятых/прошедших; фильтр по дате; время в поясе расписания | Теги статусов, фильтр на клиенте, `meta.timezone` | `booking-ui` (Slot list…, Display in schedule timezone, Date filter) |
| 4. Форма имя/email для свободного слота, проверка в браузере; отмена с подтверждением | Диалог, клиентская валидация, ConfirmDialog | `booking-ui` (Booking form, Cancel with confirmation) |
| 4. Загрузка, блокировка действий, 422 по полям, 409 с причиной и обновлением, сбой сети/сервера с повтором | Соответствующие требования | `booking-ui` (Loading and blocked actions, Validation errors, Conflict handling, Network and server errors…) |
| 4. Обновление без перезагрузки; API в composable/модуле; состояние на реактивности Vue | `bookingApi.js`, `useBookingSlots.js` | `booking-ui` (Successful create and cancel…, API access isolated…) |
| 5. Два теста PHPUnit/Pest: успешная бронь; 409 и одна активная бронь | Два обязательных теста | `backend-tests` |
| 5. Репозиторий: backend, frontend, миграции, Seeder, тесты | Раскладка | `project-setup` (Repository layout) |
| 5. README: зависимости, окружение, команды, уникальность, время, незавершённое | Раздел за разделом | `project-readme` |
| Защита: объяснить код, правка за 10–15 минут | Простой код без лишних абстракций | `openspec/config.yaml` (context), design-документы |

## Допущения, не заданные ТЗ (требуют согласования)

1. Laravel 12 (ТЗ: 11+), PHP ^8.2.
2. `BOOKING_TIMEZONE=Europe/Moscow` в `.env.example` (ТЗ пояс не называет). `app.timezone` зафиксирован в UTC.
3. Статус `past` приоритетнее `booked`; `booking_id` для прошедшего слота остаётся.
4. Повторная отмена → 404 «Бронь уже отменена», неизвестный ID → 404 «Бронь не найдена» (решение владельца; ТЗ допускает 204 или 404).
5. Отмена брони начавшегося слота на backend разрешена, в UI действие скрыто.
6. `GET /api/slots` отдаёт все слоты без пагинации.
7. Язык интерфейса, сообщений API и README — русский.
8. JavaScript вместо TypeScript, PHPUnit вместо Pest, Nuxt 3 в режиме SPA (`ssr: false`) со стандартной расширяемой структурой (`pages/`, `layouts/`, `services/`) — выбор владельца, PrimeVue подключается плагином без `@primevue/nuxt-module`.
