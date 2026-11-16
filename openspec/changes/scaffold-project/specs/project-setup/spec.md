# Spec Delta

## Purpose
Определяет раскладку репозитория, версии стека и окружение, чтобы backend и frontend запускались воспроизводимо с чистого клона.

## ADDED Requirements

### Requirement: Repository layout
Репозиторий SHALL содержать независимые каталоги `backend/` (Laravel) и `frontend/` (Vue 3 + Vite), каталог `openspec/` и корневой `README.md`.

#### Scenario: Чистый клон
- **WHEN** репозиторий клонирован и установлены PHP 8.2+, Composer и Node.js
- **THEN** backend и frontend устанавливаются и запускаются командами из README независимо друг от друга

### Requirement: Supported stack versions
Backend MUST работать на PHP 8.2 и выше и Laravel 11 и выше (выбран Laravel 12, требование `php ^8.2`). Frontend MUST использовать Vue 3, Composition API и `<script setup>`, а также PrimeVue 4.

#### Scenario: Версии зафиксированы
- **WHEN** открыты `backend/composer.json` и `frontend/package.json`
- **THEN** ограничения версий соответствуют выбору выше, а `composer.lock` и `package-lock.json` закоммичены

### Requirement: Database is SQLite by default
Окружение по умолчанию SHALL использовать SQLite-файл без дополнительных сервисов; миграции и код MUST оставаться переносимыми на MySQL и PostgreSQL (без SQLite-специфичного SQL).

#### Scenario: Запуск без внешней БД
- **WHEN** из `.env.example` создан `.env` и выполнены миграции
- **THEN** создаётся файл `backend/database/database.sqlite` и внешние сервисы не требуются

### Requirement: Environment configuration
`backend/.env.example` SHALL задавать `APP_TIMEZONE` явно (по умолчанию `Europe/Moscow`), `DB_CONNECTION=sqlite` и `APP_LOCALE=ru`. Время расписания MUST определяться значением `app.timezone`.

#### Scenario: Часовой пояс задан явно
- **WHEN** разработчик копирует `.env.example` в `.env`
- **THEN** `config('app.timezone')` возвращает `Europe/Moscow`, и изменение `APP_TIMEZONE` меняет часовой пояс расписания

### Requirement: API routes without authentication
Backend SHALL подключать `routes/api.php` (префикс `/api`) без аутентификации и без Sanctum.

#### Scenario: Публичные API-роуты
- **WHEN** клиент вызывает любой `/api/*` без токена
- **THEN** запрос обрабатывается без запроса авторизации

### Requirement: JSON errors for API
Все ответы под `/api/*`, включая ошибки валидации, 404 и 5xx, MUST возвращаться в JSON даже без заголовка `Accept: application/json`.

#### Scenario: Неизвестный маршрут
- **WHEN** клиент запрашивает `GET /api/unknown` без заголовка `Accept`
- **THEN** ответ имеет статус 404 и JSON-тело с полем `message`

### Requirement: Dev run without CORS
Frontend dev-сервер SHALL проксировать `/api` на backend (`http://localhost:8000`), чтобы в разработке не требовалась настройка CORS.

#### Scenario: Запрос с frontend
- **WHEN** страница, открытая на dev-сервере Vite, вызывает `/api/slots`
- **THEN** запрос доходит до backend на порту 8000
