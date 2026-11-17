# Spec Delta

## MODIFIED Requirements

### Requirement: List slots
`GET /api/slots` SHALL вернуть 200 и JSON `{"data": [slot, ...], "meta": {"timezone": "<IANA>", "server_time": "<ISO 8601>"}}`. В `data` MUST входить все слоты с 00:00 текущего дня в `booking.timezone` в порядке возрастания `starts_at`. Слоты прошлых дней MUST оставаться в БД. `meta.timezone` MUST совпадать с `config('booking.timezone')`. Время слотов и `meta.server_time` MUST содержать UTC-смещение этого пояса.

#### Scenario: Сортировка
- **WHEN** в БД слоты созданы в произвольном порядке
- **THEN** элементы `data` идут по возрастанию времени начала

#### Scenario: Часовой пояс в meta
- **WHEN** `booking.timezone` равен `Europe/Moscow`
- **THEN** `meta.timezone` равен `Europe/Moscow`, а `meta.server_time` содержит текущее время со смещением `+03:00`

#### Scenario: Прошлые дни не возвращаются
- **WHEN** Seeder запускался 5 января и 6 января, а запрос выполняется 6 января в 10:30 по времени расписания
- **THEN** `data` содержит 63 слота с 6 января 09:00 по 12 января 18:00; слоты 5 января в ответе отсутствуют, а слоты 6 января 09:00 и 10:00 возвращаются со статусом `past`
