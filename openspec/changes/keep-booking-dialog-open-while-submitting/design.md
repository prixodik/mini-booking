# Design

## Context

`BookingDialog.vue` уже блокирует закрытие во время запроса: `:closable="!loading"` скрывает крестик, кнопка «Отмена» `:disabled="loading"`.
В PrimeVue 4.5.5 `Dialog.onKeyDown` закрывает диалог, если `event.code === 'Escape' && this.closeOnEscape`; `closable` там не проверяется.
`closeOnEscape` читается в момент нажатия, поэтому реактивное значение свойства работает и после открытия диалога.

## Decisions

Добавить `:close-on-escape="!loading"` рядом с `:closable="!loading"`. Одна строка, без обработчиков клавиатуры.

Рассмотрено: перехватывать `update:visible` и игнорировать закрытие при `loading` — закрывает и другие пути разом, но прячет причину
в обработчике; явное свойство понятнее на защите.

## Risks / Trade-offs

Нет: после ответа `loading` снова `false`, и Esc работает как раньше.

## Migration Plan

Только frontend; откат — revert коммита.
