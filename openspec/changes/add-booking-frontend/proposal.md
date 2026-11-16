# Proposal

## Why

ТЗ п. 4: одна страница, на которой пользователь видит слоты, фильтрует по дате, бронирует и отменяет, а интерфейс корректно ведёт себя при асинхронных запросах и ошибках.

## What Changes

- Одна страница Nuxt 3 / Vue 3 (`<script setup>`, Composition API, SPA) на PrimeVue 4.
- Список слотов со статусами (свободен / занят / прошёл), фильтр по дате на клиенте, время в поясе расписания.
- Диалог бронирования (имя, email, проверка в браузере), отмена с подтверждением.
- Состояния загрузки, блокировка действий на время запроса, обработка 422/409/сетевых ошибок с повтором.
- API-модуль `utils/bookingApi.js` и composable `composables/useSlots.js`; состояние — реактивность Vue.

Вне объёма: роутинг (`pages/`), Pinia, SSR, авторизация, i18n-переключатель, e2e-тесты фронтенда.

## Capabilities

### New Capabilities
- `booking-ui`: поведение страницы бронирования и её взаимодействие с API.

### Modified Capabilities

## Impact

`frontend/{app.vue,components,composables,utils,plugins}`. Зависит от `add-slots-api` и `add-bookings-api`.
