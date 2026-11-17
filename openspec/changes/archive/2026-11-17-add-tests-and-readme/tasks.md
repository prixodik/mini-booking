# Tasks

## 1. Тесты

- [x] 1.1 Настроить `phpunit.xml` (SQLite in-memory, `APP_TIMEZONE`)
- [x] 1.2 `BookingApiTest::test_books_free_future_slot` (сценарий «Успешная бронь»)
- [x] 1.3 `BookingApiTest::test_second_request_for_taken_slot_returns_409` (сценарий «Повторная бронь»)
- [x] 1.4 `php artisan test` проходит; повторить запуск с другим системным временем (проверка детерминизма) — проверено на свежем клоне 2026-11-16: проходит при `APP_TIMEZONE` в окружении UTC, Asia/Tokyo, America/Los_Angeles, Europe/Moscow, Pacific/Kiritimati и при Asia/Tokyo в `.env` (по 11 тестов, 62 проверки); время в тестах фиксируется `Carbon::setTestNow`. Системные часы НЕ менялись
- [x] 1.5 (опционально) тесты: прошедший слот, 422, отмена + повторная бронь, идемпотентность Seeder
- [x] 1.6 Дефект: на чистом клоне `php artisan test` падал (код выхода 2: «Test directory tests/Unit not found») — исправлено: из `phpunit.xml` удалён набор `Unit`; свежий клон в короткой папке: `php artisan test` → код 0, 11 тестов, 62 проверки
- [x] 1.7 Дефект: тесты зависели от `APP_TIMEZONE` — исправлено: в `phpunit.xml` `APP_TIMEZONE=Europe/Moscow` задан принудительно и через `<env>`, и через `<server>` (с `force="true"`; одного `<env>` недостаточно: при `variables_order=GPCS` Laravel читает значение из `$_SERVER`, и переменная окружения побеждала — проверено); результат см. 1.4
- [x] 1.8 README: добавлен раздел «Если установка идёт долго или падает» (медленный `composer install` без GitHub-токена: `composer config -g github-oauth.github.com <токен>`, без записи токена; «Filename too long» на Windows: короткий путь или `core.longpaths`)

## 2. README

- [x] 2.1 Зависимости, установка, `.env`, миграции, Seeder, запуск backend и frontend, тесты
- [x] 2.2 Разделы «Уникальность бронирования» и «Какое время использует приложение»
- [x] 2.3 Раздел «Допущения и незавершённое»
- [x] 2.4 Прогнать README на чистом клоне, исправить расхождения — свежий клон 2026-11-16 (backend: composer install 518 с, `cp .env.example .env`, `key:generate`, sqlite, `migrate`, `db:seed`, `php artisan test` → код 0; frontend: `npm ci`, `nuxt dev`); расхождения устранены (1.6, 1.8)
