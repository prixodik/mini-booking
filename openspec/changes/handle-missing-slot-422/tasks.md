# Tasks

Код не пишется до одобрения change Alex.

- [x] 1.1 `useBookingSlots.js`: ветка 422 с `errors.slot_id` → перезагрузка списка и `conflict` по `design.md`
- [x] 1.2 `pages/index.vue`: удалить запись `errors.slot_id` в `generalError`
- [x] 2.1 Браузер, перехват `fetch` (ответ 422 `{"errors":{"slot_id":["Выбранный слот не существует."]}}`): диалог закрыт, тост с сообщением, список перезагружен — сценарий «Слот больше не существует»
- [x] 2.2 Ответ 422 с `errors.customer_email`: ошибка под полем, кнопка «Забронировать» — сценарий «Ошибка email с сервера»; `npx -y @fission-ai/openspec@2.0.0 validate --all --strict`
