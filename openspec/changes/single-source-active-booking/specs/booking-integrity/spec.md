# Spec Delta

## MODIFIED Requirements

### Requirement: Active booking relation
Модель `Slot` SHALL предоставлять связь с активным бронированием, которое не более одного. Активное бронирование MUST определяться по полю `active_slot_id` (бронирование, у которого `active_slot_id` равен ID слота), то есть тем же полем, на котором стоит UNIQUE-индекс, а не отдельной проверкой `cancelled_at`.

#### Scenario: Слот с отменённой бронью
- **WHEN** у слота есть только отменённые бронирования
- **THEN** связь активного бронирования возвращает пустое значение

#### Scenario: Бронь из фабрики по умолчанию
- **WHEN** в тесте бронь создаётся `Booking::factory()` без дополнительных состояний
- **THEN** у неё `active_slot_id` равен `slot_id`, `cancelled_at` равен NULL, и `GET /api/slots` показывает слот как `booked` с её `booking_id`

#### Scenario: Отменённая бронь из фабрики
- **WHEN** бронь создаётся `Booking::factory()->cancelled()`
- **THEN** у неё `cancelled_at` заполнен, `active_slot_id` равен NULL, и будущий слот в `GET /api/slots` имеет `status = available`
