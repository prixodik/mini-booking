# Tasks

## 1. Эндпоинт

- [x] 1.1 Маршрут `GET /api/slots` → `SlotController@index`
- [x] 1.2 `SlotResource`: поля `id`, `starts_at`, `ends_at` (ISO 8601 в поясе расписания), `status`, `booking_id`
- [x] 1.3 Логика статуса (`past` / `booked` / `available`) на `now()`; `meta.timezone`, `meta.server_time`
- [x] 1.4 Eager loading `activeBooking`, сортировка по `starts_at`

## 2. Проверка

- [x] 2.1 Вручную: после `migrate:fresh --seed` `GET /api/slots` отдаёт 63 слота, сегодняшние прошедшие помечены `past`
- [x] 2.2 Убедиться, что в JSON нет имени и email
