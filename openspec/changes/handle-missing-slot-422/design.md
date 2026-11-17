# Design

## Context

- `useBookingSlots.run()`: для 422 возвращает `{ status: 'validation', errors }` без перезагрузки списка; для 409/404 —
  перезагружает список и возвращает `conflict`/`notfound`.
- `pages/index.vue`, `submitBooking`: при `validation` кладёт `errors` в `serverErrors`, а `errors.slot_id[0]` — в `generalError`;
  `BookingDialog` при непустом `generalError` подписывает кнопку «Повторить».

## Decisions

1. Решение «это конфликт» принимается в composable, рядом с остальной классификацией ошибок:
   ```js
   if (error.isValidation && error.errors?.slot_id) {
     // Слота больше нет: ошибка не в полях формы, а в устаревшем списке — ведём себя как при 409.
     await load({ silent: true })
     return { status: 'conflict', message: error.errors.slot_id[0] }
   }
   if (error.isValidation) { ... как сейчас }
   ```
   Проверка стоит перед общей веткой `isValidation`. Если в том же ответе есть и ошибки полей, они не показываются: форма
   закрывается, потому что бронировать этот слот всё равно нельзя.
2. `pages/index.vue`: строка `if (result.errors.slot_id) generalError.value = ...` удаляется. Ветка `conflict` уже закрывает диалог
   и показывает тост «Не удалось забронировать» с сообщением — новый код на странице не нужен.
3. `BookingDialog.vue` не меняется: «Повторить» показывается только при `generalError`, который теперь выставляется только для сбоев сети/сервера.

## Risks / Trade-offs

Нет: случай редкий (слот удалили между загрузкой списка и отправкой), поведение совпадает с 409, к которому пользователь уже привык.

## Migration Plan

Только frontend; откат — revert коммита. Архивировать после `add-booking-frontend` (дельта `MODIFIED`).
