# Tasks

Код не пишется до одобрения change Alex.

- [ ] 1.0 Решение Alex: регулярка на сервере (рекомендуется) или `email:filter`
- [ ] 1.1 Тест `test_email_format_matches_the_browser_check` в `BookingApiTest`: `a@b`, `a@localhost` → 422 `errors.customer_email`; `иван@пример.рф` → 201 — сценарии «Домен без точки», «Кириллический адрес»; до правки тест падает
- [ ] 1.2 `StoreBookingRequest`: `EMAIL_FORMAT`, правило `regex`, сообщение `customer_email.regex` по `design.md`
- [ ] 1.3 `BookingDialog.vue`: комментарий у `EMAIL_RE` со ссылкой на серверное правило
- [ ] 2.1 `php artisan test` — все зелёные; браузер: `a@b` — ошибка без запроса, `a@b..c` — 422 под полем — сценарии `booking-ui`; `npx -y @fission-ai/openspec@2.0.0 validate --all --strict`
