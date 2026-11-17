# Tasks

Код не пишется до одобрения change Alex.

- [ ] 1.1 Тест `test_slot_id_must_be_a_json_integer` в `BookingApiTest`: `slot_id: true` и `slot_id: "5"` → 422 с `errors.slot_id`, броней 0 — сценарии «Логическое значение вместо ID», «Строка вместо числа»; до правки тест падает
- [ ] 1.2 `StoreBookingRequest`: `integer:strict` по `design.md`
- [ ] 2.1 `php artisan test` — все тесты зелёные; README, таблица «API»: «`slot_id` — целое число JSON»; `npx -y @fission-ai/openspec@2.0.0 validate --all --strict`
