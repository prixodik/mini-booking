# Design

## Context

`useBookingSlots.run()` ставит общий флаг `submitting` на время любого действия; `SlotList`/`SlotCard` получают его как `busy`
и отключают все кнопки. Какой именно слот отменяется, сейчас нигде не хранится, поэтому показать спиннер на нужной кнопке нельзя.

## Goals / Non-Goals

**Goals:** спиннер на кнопке отменяемого слота; минимум кода; поведение ошибок не меняется.

**Non-Goals:** индикатор на карточке при бронировании (он уже есть в диалоге); оптимистичное обновление списка.

## Decisions

1. `useBookingSlots.js`: `const cancellingSlotId = ref(null)`. В `cancel(slot)` перед `run(...)` ставится `slot.id`, в `finally`
   сбрасывается в `null`. `run` не меняется. Если `run` вернул `busy` (уже идёт другой запрос), ID не ставится: проверка `submitting`
   выполняется до присваивания.
   ```js
   async function cancel(slot) {
     if (submitting.value) return { status: 'busy' }
     cancellingSlotId.value = slot.id
     try {
       return await run(() => cancelBooking(slot.booking_id))
     } finally {
       cancellingSlotId.value = null
     }
   }
   ```
   `cancellingSlotId` возвращается из composable.
2. `pages/index.vue` передаёт `:cancelling-slot-id="cancellingSlotId"` в `BookingSlotList`; `SlotList.vue` — `:cancelling="slot.id === cancellingSlotId"` в `SlotCard`.
3. `SlotCard.vue`: новый prop `cancelling: Boolean`; у кнопки «Отменить бронь» `:loading="cancelling"` (PrimeVue `Button` при `loading`
   показывает спиннер вместо иконки и сам становится неактивным) и `:disabled="busy"` как сейчас.
4. Спиннер держится, пока идёт и тихая перезагрузка списка после ответа (`run` ждёт `load({ silent: true })`): так кнопка не мигает
   до обновления статуса слота.

Почему не один общий спиннер на странице: ТЗ требует показать загрузку действия, а пользователь смотрит на кнопку, которую нажал.

## Risks / Trade-offs

- Если отмена вернула ошибку и открылся диалог «Повторить», спиннер уже снят (`finally`), при повторе он появится снова — ожидаемо.

## Migration Plan

Только frontend; откат — revert коммита. Архивировать после `add-booking-frontend` (дельта `MODIFIED`).
