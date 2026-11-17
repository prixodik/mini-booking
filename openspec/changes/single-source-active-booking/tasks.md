# Tasks

Код не пишется до одобрения change Alex.

## 1. Тесты

- [x] 1.1 `SlotsApiTest`: тест `test_default_factory_booking_marks_slot_booked_and_cancelled_one_does_not` — сценарии «Бронь из фабрики по умолчанию» и «Отменённая бронь из фабрики»; до правки фабрики тест падает (у брони по умолчанию `active_slot_id` NULL; состояния `cancelled()` нет) — проверено: до правки «Call to undefined method cancelled()»

## 2. Реализация

- [x] 2.1 `BookingFactory`: `active_slot_id` в `definition()`, `forSlot()` без `active_slot_id`, состояние `cancelled()` по `design.md`
- [x] 2.2 `Slot::activeBooking()` по `active_slot_id` с PHPDoc по `design.md`

## 3. Проверка

- [x] 3.1 `php artisan test` — все тесты зелёные (в т. ч. «Слот с отменённой бронью», `test_cancelled_booking_makes_future_slot_available_again`)
- [x] 3.2 README, раздел «Как обеспечена уникальность бронирования»: строка о том, что статус слота в API считается по тому же `active_slot_id`; `npx -y @fission-ai/openspec@2.0.0 validate --all --strict`
