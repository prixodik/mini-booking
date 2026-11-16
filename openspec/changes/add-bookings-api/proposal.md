# Proposal

## Why

ТЗ п. 3: создание и отмена бронирований с валидацией, корректными статусами 201/409/422/204 и гарантией ровно одной активной брони на слот при одновременных запросах.

## What Changes

- `POST /api/bookings` (`slot_id`, `customer_name`, `customer_email`): 201 с ID брони, 422 по полям, 409 для занятого или прошедшего слота.
- `DELETE /api/bookings/{id}`: 204; неизвестный ID — 404; повторная отмена — тоже 404 (с другим сообщением).
- Гарантия уникальности опирается на UNIQUE-индекс (`booking-integrity`); нарушение индекса превращается в 409.
- Валидация через `FormRequest`; бизнес-логика создания/отмены — в одном сервисном классе.

Вне объёма: авторизация (отмена по известному ID без неё — осознанное допущение демо, п. 3 ТЗ), письма, лимиты на число броней.

## Capabilities

### New Capabilities
- `bookings-api`: контракт и поведение создания и отмены бронирований.

### Modified Capabilities

## Impact

`routes/api.php`, `BookingController`, `StoreBookingRequest`, `BookingService`. Зависит от `add-data-model-and-seeding`.
