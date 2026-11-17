# Tasks

Код не пишется до одобрения change Alex.

- [ ] 1.1 `BookingDialog.vue`: `:close-on-escape="!loading"`
- [ ] 2.1 Браузер с замедленным `POST /api/bookings` (перехват `fetch`, ответ 422): Esc во время отправки — диалог открыт, после ответа ошибки под полями — сценарий «Esc во время отправки»
- [ ] 2.2 После ответа Esc закрывает диалог — сценарий «Esc после ответа»; `npx -y @fission-ai/openspec@2.0.0 validate --all --strict`
