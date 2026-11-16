# Design

## Context

Backend отдаёт плоский список слотов с `status`, `booking_id` и `meta.timezone` (см. `add-slots-api`). Статусы 422/409/404/204 описаны в `add-bookings-api`.

## Goals / Non-Goals

**Goals:** предсказуемая работа с асинхронными запросами; мало кода; готовые компоненты PrimeVue.

**Non-Goals:** роутинг, глобальное хранилище, анимации, e2e-тесты.

## Decisions

### Структура

```
frontend/                         Nuxt 3 (ssr: false)
├── nuxt.config.js                ssr: false, css, nitro.devProxy для /api
├── app.vue                       <NuxtLayout><NuxtPage/></NuxtLayout>, глобальные <Toast/> и <ConfirmDialog/>
├── layouts/default.vue           каркас: шапка, контейнер, адаптивные отступы
├── pages/index.vue               страница бронирования `/` — тонкая: useSlots() + компоненты
├── components/booking/           компоненты функциональности «бронирование»
│   ├── SlotList.vue              слоты выбранной даты
│   ├── DateFilter.vue            фильтр по дате
│   └── BookingDialog.vue         форма имя/email, ошибки полей
├── composables/useSlots.js       slots, timezone, selectedDate, loading, submitting, error, действия
├── services/httpClient.js        общий fetch-запрос, заголовки, класс ApiError {status, message, errors}
├── services/bookingApi.js        getSlots(), createBooking(), cancelBooking()
├── utils/datetime.js             форматирование в поясе расписания через Intl.DateTimeFormat({timeZone})
├── plugins/primevue.js           PrimeVue + тема Aura (@primeuix/themes) + ToastService + ConfirmationService
└── assets/css/main.css           общие стили
```

Принципы расширяемости: новая страница — файл в `pages/`; новая функциональность — своя папка в `components/<feature>/`, свой composable и сервис в `services/`; `httpClient.js` общий для всех сервисов. Автоимпорт Nuxt подхватывает `composables/`, `utils/` и `components/` (с префиксом папки: `components/booking/SlotList.vue` → `<BookingSlotList/>`); `services/` импортируются явно (`~/services/bookingApi`), импорты PrimeVue-компонентов — явные (`import Button from 'primevue/button'`), чтобы не зависеть от `@primevue/nuxt-module`. Запросы выполняются на клиенте (SPA), поэтому используется `fetch` внутри `services/`, а не `useFetch` с SSR-кэшем.

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
