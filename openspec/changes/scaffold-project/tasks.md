# Tasks

## 1. Backend

- [ ] 1.1 `composer create-project laravel/laravel:^12.0 backend`; убрать лишнее (без Sanctum, без стартовых страниц, если мешают)
- [ ] 1.2 Настроить `.env.example`: `APP_TIMEZONE=Europe/Moscow`, `APP_LOCALE=ru`, `DB_CONNECTION=sqlite`
- [ ] 1.3 Подключить `routes/api.php` в `bootstrap/app.php`; включить JSON-рендеринг ошибок для `api/*`
- [ ] 1.4 Проверить: `php artisan migrate` создаёт SQLite-файл; `GET /api/unknown` → 404 JSON

## 2. Frontend

- [ ] 2.1 Создать Vue 3 проект в `frontend/` (Vite, JavaScript, без Router/Pinia)
- [ ] 2.2 Установить `primevue@4`, `@primeuix/themes`, `primeicons`; подключить тему и сервисы Toast/Confirmation в `main.js`
- [ ] 2.3 Настроить dev-прокси `/api` → `http://localhost:8000` в `vite.config.js`

## 3. Репозиторий

- [ ] 3.1 Скелет корневого `README.md` (структура, процесс OpenSpec)
- [ ] 3.2 Проверить, что оба приложения запускаются на чистом клоне
