# Spec Delta

## Purpose
Определяет контракт эндпоинта списка слотов, который читает frontend.

## ADDED Requirements

### Requirement: List slots
`GET /api/slots` SHALL возвращать статус 200 и JSON вида `{"data": [slot, ...], "meta": {"timezone": "<IANA>", "server_time": "<ISO 8601>"}}`. Слоты MUST быть отсортированы по `starts_at` по возрастанию и включать все слоты в БД, в том числе прошедшие.

#### Scenario: Сортировка
- **WHEN** в БД слоты созданы в произвольном порядке
- **THEN** элементы `data` идут по возрастанию времени начала

#### Scenario: Часовой пояс в meta
- **WHEN** `app.timezone` равен `Europe/Moscow`
- **THEN** `meta.timezone` равен `Europe/Moscow`, а `meta.server_time` содержит текущее время с UTC-смещением

### Requirement: Slot representation
Каждый слот SHALL содержать поля: `id` (целое), `starts_at` и `ends_at` (ISO 8601 с UTC-смещением пояса расписания, например `2026-11-16T09:00:00+03:00`), `status` (`available`, `booked` или `past`) и `booking_id` (целое или `null`).

#### Scenario: Формат времени
- **WHEN** слот 09:00–10:00 по Москве запрошен через API
- **THEN** `starts_at` равен `…T09:00:00+03:00`, а `ends_at` равен `…T10:00:00+03:00`

### Requirement: Slot status
`status` MUST быть `past`, если `starts_at` меньше или равен текущему серверному времени, независимо от наличия брони. Иначе `status` MUST быть `booked`, если у слота есть активная бронь, и `available`, если её нет.

#### Scenario: Свободный будущий слот
- **WHEN** слот в будущем и без активной брони
- **THEN** `status` равен `available`, а `booking_id` равен `null`

#### Scenario: Занятый будущий слот
- **WHEN** слот в будущем и имеет активную бронь
- **THEN** `status` равен `booked`, а `booking_id` равен ID этой брони

#### Scenario: Прошедший слот с бронью
- **WHEN** слот уже начался и у него есть активная бронь
- **THEN** `status` равен `past`, а `booking_id` равен ID этой брони

#### Scenario: Отменённая бронь
- **WHEN** у будущего слота есть только отменённая бронь
- **THEN** `status` равен `available`, а `booking_id` равен `null`

### Requirement: No personal data in slots
Ответ `GET /api/slots` MUST NOT содержать имя и email клиента.

#### Scenario: Занятый слот
- **WHEN** слот занят бронью с именем и email
- **THEN** в JSON слота нет полей и значений с именем или email

### Requirement: Efficient loading
Список слотов SHALL загружаться без запросов в цикле (N+1): активные брони подгружаются одним запросом.

#### Scenario: Количество запросов
- **WHEN** запрашиваются 63 слота
- **THEN** выполняется не больше двух SQL-запросов к данным слотов и броней
