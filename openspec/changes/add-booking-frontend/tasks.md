# Tasks

## 0. Каркас

- [ ] 0.1 `app.vue` (`<NuxtLayout><NuxtPage/></NuxtLayout>`, `<Toast/>`, `<ConfirmDialog/>`), `layouts/default.vue` (шапка, контейнер), `pages/index.vue` (тонкая страница)

## 1. API и состояние

- [ ] 1.1 `services/bookingApi.js` (+ `services/httpClient.js`: общий запрос, `ApiError`): `getSlots`, `createBooking`, `cancelBooking`, `ApiError` (status, message, errors)
- [ ] 1.2 `utils/datetime.js`: форматирование даты/времени и ключа даты в поясе расписания
- [ ] 1.3 `composables/useSlots.js`: загрузка, выбранная дата, `loading`/`submitting`, действия `book` / `cancel`, повтор последнего неудавшегося действия

## 2. Интерфейс

- [ ] 2.1 `components/booking/SlotList.vue`: слоты выбранной даты, `Tag` статуса, кнопки «Забронировать» / «Отменить бронь»
- [ ] 2.2 `components/booking/DateFilter.vue` — фильтр по дате (`SelectButton`), дата по умолчанию — сегодня в поясе расписания
- [ ] 2.3 `components/booking/BookingDialog.vue`: поля имя/email, проверка в браузере, ошибки 422 под полями, блокировка на время запроса
- [ ] 2.4 `ConfirmDialog` для отмены, `Toast` для успеха и ошибок, индикатор загрузки
- [ ] 2.5 Ошибки: 409 (сообщение + обновление), сеть/5xx («Повторить»), 404 при отмене

## 3. Проверка

- [ ] 3.1 Вручную в браузере на 1280 px и 360 px: сценарии создания, отмены, 422, 409 (две вкладки), остановленный backend
- [ ] 3.2 Проверить отсутствие `fetch` в `components/`, `layouts/`, `pages/` и `app.vue`
