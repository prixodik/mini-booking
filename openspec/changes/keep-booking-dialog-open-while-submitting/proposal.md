# Proposal

## Why

Ревью (`review-mini-booking.md`, И-4): пока идёт `POST /api/bookings`, у диалога брони скрыт крестик (`:closable="!loading"`),
но клавиша Esc его всё равно закрывает: PrimeVue `Dialog` обрабатывает Esc по свойству `closeOnEscape`, независимо от `closable`
(`node_modules/primevue/dialog/index.mjs`, `onKeyDown`). Запрос продолжается: при 201 появляется тост, при 422 ошибки полей
пишутся в закрытый диалог и теряются при следующем открытии. ТЗ, п. 4: «блокируйте действие, пока его запрос выполняется» и
«покажите ошибки полей при 422». Alex попросил исправить.

## What Changes

- `BookingDialog.vue`: `:close-on-escape="!loading"` — во время отправки Esc диалог не закрывает; после ответа Esc работает как раньше.

Вне объёма: остальные пункты ревью, изменения API и backend.

## Capabilities

### New Capabilities

### Modified Capabilities
- `booking-ui`: диалог брони нельзя закрыть, пока выполняется запрос (новое требование).

## Impact

`frontend/components/booking/BookingDialog.vue` (одна строка). Backend и API не затрагиваются.

## Decisions of Alex

- Исправить И-4 из отчёта ревью (17.11.2026).

## Open Questions

Нет: нужно только одобрение change.
