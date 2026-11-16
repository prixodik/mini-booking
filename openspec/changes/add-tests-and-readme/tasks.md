# Tasks

## 1. Тесты

- [x] 1.1 Настроить `phpunit.xml` (SQLite in-memory, `APP_TIMEZONE`)
- [x] 1.2 `BookingApiTest::test_books_free_future_slot` (сценарий «Успешная бронь»)
- [x] 1.3 `BookingApiTest::test_second_request_for_taken_slot_returns_409` (сценарий «Повторная бронь»)
- [ ] 1.4 `php artisan test` проходит; повторить запуск с другим системным временем (проверка детерминизма) — на чистом клоне 2026-11-16: `--testsuite=Feature` проходит (11 тестов, 62 проверки); системное время менять нельзя и не менялось; время в тестах фиксируется `Carbon::setTestNow` (прочитано в коде всех трёх классов); при другом `APP_TIMEZONE` (UTC, Asia/Tokyo, America/Los_Angeles) падает 1 тест (см. 1.7)
- [x] 1.5 (опционально) тесты: прошедший слот, 422, отмена + повторная бронь, идемпотентность Seeder
- [ ] 1.6 Дефект: на чистом клоне `php artisan test` падает (код выхода 2: «Test directory tests/Unit not found»), потому что `phpunit.xml` содержит набор `Unit`, а каталога `tests/Unit` в git нет (пустые каталоги не хранятся); обходы: `mkdir tests/Unit` или `--testsuite=Feature`. Исправление по спецификации («Runs on a clean clone»): убрать набор `Unit` из `phpunit.xml` (или добавить каталог в git), проверить на свежем клоне
- [ ] 1.7 Дефект: `phpunit.xml` не фиксирует `APP_TIMEZONE`, тесты читают `.env`; тест `test_lists_slots_sorted_with_statuses_offset_and_without_personal_data` (`SlotsApiTest`) жёстко ждёт `+03:00` и `Europe/Moscow`, поэтому при другом `APP_TIMEZONE` падает (README разрешает его менять). Исправление по спецификации («Independent of local timezone setting»): принудительно задать `APP_TIMEZONE=Europe/Moscow` в `phpunit.xml`, проверить запуск при UTC, Asia/Tokyo, America/Los_Angeles
- [ ] 1.8 README: описать медленный `composer install` без GitHub-токена («Could not authenticate against github.com», пакеты ставятся из исходников; 324 с, 112 предупреждений) и ошибку «Filename too long» при длинном пути на Windows (`git config --global core.longpaths true` или короткий путь)

## 2. README

- [x] 2.1 Зависимости, установка, `.env`, миграции, Seeder, запуск backend и frontend, тесты
- [x] 2.2 Разделы «Уникальность бронирования» и «Какое время использует приложение»
- [x] 2.3 Раздел «Допущения и незавершённое»
- [ ] 2.4 Прогнать README на чистом клоне, исправить расхождения — прогон 2026-11-16 выполнен, найдены расхождения (см. 1.6, 1.8): `php artisan test` из README на чистом клоне завершается ошибкой, `composer install` без GitHub-токена очень долгий
