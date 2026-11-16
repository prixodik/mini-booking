# Proposal

## Why

Нужен воспроизводимый каркас: backend (Laravel) и frontend (Vue 3) в одном репозитории, которые запускаются
одной-двумя командами. Без каркаса остальные changes не на чем реализовывать. ТЗ оценивает «воспроизводимость запуска».

## What Changes

- Каталог `backend/` — Laravel 12 (PHP ^8.2), SQLite, API-роуты включены без Sanctum/авторизации.
- Каталог `frontend/` — Vue 3 + Vite (SPA, JavaScript), PrimeVue 4, dev-прокси `/api` → backend.
- `backend/.env.example` с `APP_TIMEZONE`, `DB_CONNECTION=sqlite`, локализацией `ru`.
- Единый JSON-формат ошибок для `/api/*`, независимо от заголовка `Accept`.
- Скелет `README.md` (структура, процесс OpenSpec); полный README — в `add-tests-and-readme`.

Вне объёма (п. 1 ТЗ): авторизация, админка, управление комнатами, Scheduler/cron.

## Capabilities

### New Capabilities
- `project-setup`: раскладка репозитория, версии зависимостей, окружение и dev-запуск backend и frontend.

### Modified Capabilities

## Impact

Создаются `backend/` и `frontend/`; правятся `bootstrap/app.php`, `.env.example`, `vite.config.js`. Прикладной логики нет.
