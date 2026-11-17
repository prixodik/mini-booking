# Design

## Context

`load()` начинается с `loadError.value = null`; шаблон показывает блок ошибки по `v-if="loadError"`, а кнопка «Повторить»
внутри него уже имеет `:loading="loading"`. Общий спиннер — `v-if="loading && !slots.length"`, то есть только для пустого списка.

## Decisions

Перенести `loadError.value = null` из начала `load()` в ветку успеха, после проверки `seq === loadSeq`
(change `ignore-stale-slot-loads`):

```js
const response = await getSlots()
if (seq !== loadSeq) return false
loadError.value = null // сообщение об ошибке убираем только после успешного ответа
slots.value = response.data
```

Блок ошибки остаётся смонтированным, кнопка «Повторить» показывает спиннер за счёт уже существующего `:loading="loading"`.
Тихие перезагрузки (`silent`) `loading` не включают, поэтому после успешного действия при висящей ошибке кнопка без спиннера,
но сообщение исчезнет при успешном ответе — так и нужно.

Рассмотрено: показывать общий спиннер и при непустом списке — заметнее, но двигает раскладку и дублирует индикатор на кнопке.

## Risks / Trade-offs

Нет: меняется только момент сброса ошибки.

## Migration Plan

Только frontend; откат — revert коммита.
