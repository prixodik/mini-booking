# Design

## Context

Backend отдаёт плоский список слотов с `status`, `booking_id` и `meta.timezone` (см. `add-slots-api`). Статусы 422/409/404/204 описаны в `add-bookings-api`.

## Goals / Non-Goals

**Goals:** предсказуемая работа с асинхронными запросами; мало кода; готовые компоненты PrimeVue.

**Non-Goals:** роутинг, глобальное хранилище, анимации, e2e-тесты.

## Decisions

### Структура

```
frontend/src/
├── main.js                     PrimeVue + тема (Aura из @primeuix/themes) + Toast/Confirmation
├── App.vue                     страница: заголовок, фильтр даты, список, диалоги
├── api/bookingApi.js           fetch-обёртка: getSlots(), createBooking(), cancelBooking(); класс ApiError {status, message, errors}
├── composables/useSlots.js     slots, timezone, selectedDate, loading, error, pending, actions
├── components/SlotList.vue     список слотов выбранной даты
├── components/BookingDialog.vue  форма имя/email, ошибки полей
└── utils/datetime.js           форматирование в поясе расписания через Intl.DateTimeFormat({timeZone})
```

### Компоненты PrimeVue

`Button`, `Tag` (статусы), `Dialog` + `InputText` + `Message` (форма и ошибки), `ConfirmDialog` + `useConfirm` (отмена), `Toast` + `useToast` (успех/ошибка), `ProgressSpinner` или `Skeleton` (загрузка), `SelectButton` (фильтр даты, значения `YYYY-MM-DD` в поясе расписания). Вёрстка слотов — адаптивная сетка карточек/строк (`flex-wrap`/CSS grid), а не широкая таблица, чтобы не было горизонтальной прокрутки на телефоне.

### Состояние и запросы

- Один `useSlots()` на страницу. Состояние: `slots`, `timezone`, `selectedDate`, `loading` (загрузка списка), `submitting` (создание/отмена), `lastFailedAction` (замыкание для «Повторить»).
- Даты считаются в поясе расписания через `Intl.DateTimeFormat('sv-SE', {timeZone, ...})` — это даёт `YYYY-MM-DD` без внешних библиотек. Сравнение «сегодня» — тоже через пояс расписания, а не `new Date().toISOString()`.
- Классификация ошибок в `ApiError`: `status 422` → поля, `409` → сообщение + обновление списка, `404` при отмене → обновить список, сеть/5xx → ошибка с «Повторить».
- Запрос выполняется только если нет активного (`submitting`) — защита от двойной отправки поверх `:disabled` на кнопках.
- Заголовки `Accept: application/json` и `Content-Type: application/json` ставит API-модуль.

### Язык интерфейса

Русский; строки в компонентах, без i18n-библиотеки.

## Risks / Trade-offs

- Список обновляется только после действий и повторной загрузки; устаревание списка между действиями исправляется ответом 409 + перезагрузкой. Автообновление по таймеру не делается.
- Статус `past` приходит с сервера на момент загрузки; слот может стать прошедшим, пока страница открыта. Сервер всё равно ответит 409, а UI обновит список.
- `Intl.DateTimeFormat` с `timeZone` поддерживается современными браузерами; старые не поддерживаются.
