# Tasks

Код не пишется до одобрения change Alex.

- [ ] 1.1 `nuxt.config.js`: `imports.autoImport: false`, `components.dirs: []` с комментарием
- [ ] 1.2 Явные импорты по таблице `design.md`: `pages/index.vue`, `BookingDialog.vue`, `DateFilter.vue`, `SlotCard.vue`, `SlotList.vue`, `useBookingSlots.js`, `plugins/primevue.js`; теги `<DateFilter>`, `<SlotList>`, `<SlotCard>`
- [ ] 2.1 `npm run dev`: в консоли браузера нет ошибок, `NuxtLayout`/`NuxtPage` работают; проверка «Забытый импорт» — временно убрать импорт `computed` в `SlotCard.vue`, увидеть ошибку, вернуть
- [ ] 2.2 Основные сценарии `booking-ui` в браузере — сценарий «Поведение не изменилось»; поиск `Booking(DateFilter|SlotList|SlotCard)` во `frontend/` (кроме `node_modules`, `.nuxt`) — пусто
- [ ] 2.3 README («Структура репозитория»): «автоимпорт Nuxt выключен, все импорты явные»; `npx -y @fission-ai/openspec@2.0.0 validate --all --strict`
