# Proposal

## Why

ТЗ, п. 4: «Покажите загрузку и блокируйте действие, пока его запрос выполняется». Требование `booking-ui`
«Loading and blocked actions» требует индикатор загрузки. Для бронирования он есть (`:loading` на кнопке в диалоге), для отмены — нет:
после подтверждения в `ConfirmDialog` диалог закрывается, все кнопки становятся `disabled`, но спиннера нет, и на медленной сети
непонятно, что запрос идёт. Найдено ревью (`review-mini-booking.md`, И-1); Alex попросил исправить.

## What Changes

- `useBookingSlots` хранит ID слота, по которому сейчас выполняется отмена (`cancellingSlotId`), и сбрасывает его по завершении запроса.
- Кнопка «Отменить бронь» этого слота показывает спиннер (`loading` у PrimeVue `Button`); остальные кнопки по-прежнему отключены через `busy`.
- Поведение бронирования, обработка 404/сетевых ошибок и «Повторить» не меняются.

Вне объёма: остальные пункты ревью (Esc в диалоге брони — И-4, и др.), изменения API и backend.

## Capabilities

### New Capabilities

### Modified Capabilities
- `booking-ui`: индикатор загрузки на кнопке отмены конкретного слота.

## Impact

`frontend/composables/useBookingSlots.js`, `frontend/pages/index.vue`, `frontend/components/booking/SlotList.vue`,
`frontend/components/booking/SlotCard.vue`. Backend и API не затрагиваются.

## Decisions of Alex

- Исправить И-1 из отчёта ревью (17.11.2026).

## Open Questions

Нет: решение без вариантов, нужно только одобрение change.
