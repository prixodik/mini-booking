# Design

## Context

Инвентаризация автоимпортов (поиск по файлам, 17.11.2026):

| Файл | Через автоимпорт сейчас |
|---|---|
| `pages/index.vue` | `ref`, `onMounted`, `useBookingSlots`, `<BookingDateFilter>`, `<BookingSlotList>`, `<BookingDialog>` |
| `components/booking/BookingDialog.vue` | `ref`, `computed`, `watch`, `dateKey`, `formatTime`, `formatDateLabel` |
| `components/booking/DateFilter.vue` | `computed`, `formatDateLabel` |
| `components/booking/SlotCard.vue` | `formatTime` (`computed` уже явно) |
| `components/booking/SlotList.vue` | `<BookingSlotCard>` |
| `composables/useBookingSlots.js` | `dateKey` (`ref`, `computed`, сервисы уже явно) |
| `plugins/primevue.js` | `defineNuxtPlugin` |
| `app.vue`, `layouts/default.vue` | только `<NuxtLayout>`, `<NuxtPage>`, `<slot>` — встроенные |

## Decisions

1. `nuxt.config.js`:
   ```js
   // Явные импорты: всё, что использует файл, видно в его import (проще читать и править).
   imports: { autoImport: false },
   components: { dirs: [] },
   ```
   `components.dirs: []`, а не `components: false`: так выключается только регистрация компонентов из `components/`, а встроенные
   `NuxtLayout`/`NuxtPage` продолжают работать. Проверить запуском; если встроенные пропадут — импортировать их из `#components` в
   `app.vue` (сначала правка этого раздела).
2. Пути: `~/utils/datetime`, `~/composables/useBookingSlots`, `~/components/booking/DateFilter.vue` (с расширением `.vue`);
   `defineNuxtPlugin` — из `#app`.
3. Имя импорта компонента = имя файла: `import DateFilter from '~/components/booking/DateFilter.vue'`, в шаблоне `<DateFilter>`.
   `BookingDialog` уже совпадает с файлом. Имена БЭМ-блоков (`date-filter`, `slot-card`) не меняются.
4. Порядок `import` в файле: пакеты (`vue`, `primevue/*`), затем файлы проекта (`~/...`).

Рассмотрено: оставить автоимпорт и только дописать явные импорты — работает, но правило ничем не защищено: на защите новый код
снова заработает без импорта, и стиль разъедется.

## Risks / Trade-offs

- `npm run dev` должен пересоздать `.nuxt/` (типизация автоимпортов); при странных ошибках — `npx nuxt prepare`.
- Change затрагивает файлы, которые меняют другие frontend-changes; поэтому он идёт первым (см. `proposal.md`, «Порядок»).

## Migration Plan

Только frontend; откат — revert коммита.
