# Spec Delta

## ADDED Requirements

### Requirement: Explicit imports in the frontend
Каждый `.vue`- и `.js`-файл во `frontend/` (кроме `nuxt.config.js`) MUST явно импортировать всё, что использует: API Vue, утилиты, composables, компоненты проекта и функции Nuxt. Автоимпорт функций и компонентов проекта MUST быть выключен в `nuxt.config.js`. Компоненты проекта в шаблонах SHALL называться так же, как их файл. Встроенные компоненты Nuxt (`NuxtLayout`, `NuxtPage`) остаются доступны без импорта.

#### Scenario: Откуда взялось имя
- **WHEN** в `pages/index.vue` используется `useBookingSlots`, `ref` или `<SlotList>`
- **THEN** в блоке `<script setup>` этого файла есть `import` этого имени с путём к файлу или пакету

#### Scenario: Забытый импорт
- **WHEN** из компонента удалить строку `import { computed } from 'vue'`, а `computed` в нём используется
- **THEN** страница падает с ошибкой `computed is not defined` при разработке, а не продолжает работать за счёт автоимпорта

#### Scenario: Поведение не изменилось
- **WHEN** после правки выполняются основные сценарии `booking-ui` (загрузка, фильтр, бронь, отмена, ошибки)
- **THEN** страница работает так же, как до правки
