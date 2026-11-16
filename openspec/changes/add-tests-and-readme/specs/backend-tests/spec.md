# Spec Delta

## Purpose
Фиксирует обязательные автотесты backend и условия их детерминированности.

## ADDED Requirements

### Requirement: Test of successful booking
Набор тестов SHALL содержать тест, который бронирует свободный будущий слот через `POST /api/bookings` и проверяет статус 201, наличие `data.id` и ровно одну активную бронь этого слота в БД.

#### Scenario: Успешная бронь
- **WHEN** запускается тест успешного бронирования
- **THEN** он проходит, подтверждая 201 и одну активную бронь

### Requirement: Test of conflict on occupied slot
Набор тестов SHALL содержать тест, в котором второй `POST /api/bookings` на уже занятый слот возвращает 409, а число активных броней этого слота остаётся равным одной.

#### Scenario: Повторная бронь
- **WHEN** запускается тест конфликта
- **THEN** второй запрос получает 409 с сообщением, а в БД одна активная бронь слота

### Requirement: Deterministic time
Тесты MUST NOT зависеть от реального времени суток: «сейчас» фиксируется через `Carbon::setTestNow` (например, утро рабочего дня), чтобы свободный будущий слот существовал в любое время запуска.

#### Scenario: Запуск вечером
- **WHEN** тесты запускаются в 23:00 по реальному времени
- **THEN** они проходят так же, как и утром

### Requirement: Isolated and offline
Тесты SHALL запускаться командой `php artisan test` на SQLite in-memory с `RefreshDatabase`, без внешних сервисов и без данных от предыдущих запусков.

#### Scenario: Чистый запуск
- **WHEN** выполняется `php artisan test` после `composer install` и копирования `.env.example`
- **THEN** все тесты проходят, а рабочая SQLite-БД разработки не затрагивается

### Requirement: Runs on a clean clone
`php artisan test` SHALL завершаться успешно на чистом клоне репозитория без ручного создания каталогов. Наборы в `phpunit.xml` MUST ссылаться только на каталоги, которые есть в git.

#### Scenario: Свежий клон
- **WHEN** на свежем клоне после `composer install` и копирования `.env.example` выполняется `php artisan test`
- **THEN** команда завершается кодом 0, ошибок «Test directory not found» нет

### Requirement: Independent of local timezone setting
Результат тестов MUST NOT зависеть от `APP_TIMEZONE` в `.env` и в окружении: `phpunit.xml` SHALL принудительно задавать `APP_TIMEZONE=Europe/Moscow`, а ожидаемые значения со смещением `+03:00` относятся к этому поясу.

#### Scenario: Другой пояс в окружении
- **WHEN** `php artisan test` запускается при `APP_TIMEZONE=UTC`, `Asia/Tokyo` или `America/Los_Angeles` в окружении
- **THEN** все тесты проходят, как и при `Europe/Moscow`

### Requirement: Optional extra tests
Дополнительные тесты SHALL NOT заменять два обязательных и MAY покрывать, если остаётся время: бронь прошедшего слота (409), валидацию (422, в том числе несуществующий `slot_id`), отмену с повторной бронью, идемпотентность Seeder. Их отсутствие не нарушает ТЗ.

#### Scenario: Идемпотентность Seeder
- **WHEN** Seeder запущен дважды с зафиксированным временем
- **THEN** в БД ровно 63 слота
