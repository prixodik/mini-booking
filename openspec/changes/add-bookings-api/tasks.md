# Tasks

## 1. Создание брони

- [ ] 1.1 Маршрут `POST /api/bookings` → `BookingController@store`
- [ ] 1.2 `StoreBookingRequest`: правила `slot_id`, `customer_name`, `customer_email`; русские сообщения (`lang/ru`)
- [ ] 1.3 `BookingService::create`: проверка прошедшего слота, вставка с `active_slot_id`, перехват `UniqueConstraintViolationException`
- [ ] 1.4 `SlotUnavailableException` → 409 JSON `{"message": ...}` (через render в исключении или обработчик)
- [ ] 1.5 Ответ 201 `{"data": {"id": ...}}`

## 2. Отмена брони

- [ ] 2.1 Маршрут `DELETE /api/bookings/{id}` → `BookingController@destroy`
- [ ] 2.2 `BookingService::cancel`: `cancelled_at = now()`, `active_slot_id = null`; условный апдейт `whereNull('cancelled_at')`; повторная отмена — 404 «Бронь уже отменена»; неизвестный ID — 404 «Бронь не найдена»

## 3. Проверка вручную

- [ ] 3.1 curl-сценарий: создать → повторить (409) → отменить (204) → отменить повторно (404) → создать снова (201)
- [ ] 3.2 Прошедший слот → 409; пустое тело → 422; несуществующий `slot_id` → 422
