# Tasks

Код не пишется до одобрения change Alex.

- [x] 1.0 Решение Alex: регулярка на сервере (рекомендованный вариант 1; «все спецификации принимаю», 17.11.2026)
- [x] 1.1 Тест `test_email_format_matches_the_browser_check` в `BookingApiTest`: `a@b`, `a@localhost` → 422 `errors.customer_email`; `иван@пример.рф` → 201 — сценарии «Домен без точки», «Кириллический адрес»; до правки тест падает — проверено: до правки 201
- [x] 1.2 `StoreBookingRequest`: `EMAIL_FORMAT`, правило `regex`, сообщение `customer_email.regex` по `design.md`
- [x] 1.3 `BookingDialog.vue`: комментарий у `EMAIL_RE` со ссылкой на серверное правило
- [x] 2.1 `php artisan test` — все зелёные; браузер (проверено: `a@b` — ошибка без запроса, `a@b..c` — 422 под полем): `a@b` — ошибка без запроса, `a@b..c` — 422 под полем — сценарии `booking-ui`; `npx -y @fission-ai/openspec@2.0.0 validate --all --strict`
