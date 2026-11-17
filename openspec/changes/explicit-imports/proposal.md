# Proposal

## Why

Ревью (`review-mini-booking.md`, Ж-11): во frontend два способа подключения. `SlotCard.vue` и `useBookingSlots.js` импортируют
`ref`/`computed` из `vue` явно, а остальные файлы полагаются на автоимпорт Nuxt: `ref`, `watch`, `onMounted`, функции
`dateKey`/`formatTime`/`formatDateLabel` из `utils/`, composable `useBookingSlots`, компоненты `BookingDateFilter`, `BookingSlotList`,
`BookingDialog`, `BookingSlotCard` (имена собираются из папки и файла), `defineNuxtPlugin`. Даже `useBookingSlots.js` импортирует
`ref` явно, а `dateKey` берёт из автоимпорта. На защите (правка за 10–15 минут без ИИ) непонятно, откуда берётся имя и где искать
файл. Оценивается читаемость кода. Alex: явные импорты.

## What Changes

- Во всех `.vue` и `.js` во `frontend/` (кроме `nuxt.config.js`) всё используемое импортируется явно: API Vue из `vue`, утилиты из
  `~/utils/datetime`, composable из `~/composables/useBookingSlots`, компоненты проекта из `~/components/booking/*.vue`,
  `defineNuxtPlugin` из `#app`.
- Компоненты проекта в шаблонах называются так же, как файл: `<DateFilter>`, `<SlotList>`, `<SlotCard>`, `<BookingDialog>` вместо
  `<BookingDateFilter>`, `<BookingSlotList>`, `<BookingSlotCard>`.
- Автоимпорт выключается в `nuxt.config.js` (`imports.autoImport: false`, `components.dirs: []`), чтобы правило соблюдалось само:
  забытый импорт даёт ошибку сразу, а не работает «по волшебству».

Вне объёма: встроенные компоненты Nuxt `<NuxtLayout>`, `<NuxtPage>` (часть фреймворка, остаются как есть), TypeScript, остальные пункты ревью.

## Capabilities

### New Capabilities

### Modified Capabilities
- `project-setup`: во frontend используются только явные импорты, автоимпорт Nuxt выключен (новое требование).

## Impact

`frontend/nuxt.config.js`, `frontend/app.vue` (при необходимости), `frontend/pages/index.vue`, `frontend/components/booking/*.vue`,
`frontend/composables/useBookingSlots.js`, `frontend/plugins/primevue.js`, README (раздел «Структура репозитория», одна строка).
Поведение страницы не меняется.

Порядок: реализовать до остальных frontend-changes (`show-cancel-loading`, `ignore-stale-slot-loads`, `add-request-timeout`,
`handle-missing-slot-422`, `keep-booking-dialog-open-while-submitting`, `use-primevue-date-filter-and-tags`), чтобы они сразу
писались с явными импортами и новыми именами тегов.

## Decisions of Alex

- Явные импорты (Ж-11 из отчёта ревью, 17.11.2026).

## Open Questions

Нет, если устраивают выключение автоимпорта и переименование тегов компонентов (рекомендуется; см. `design.md`).
