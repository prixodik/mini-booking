# Design

## Context

Репозиторий пуст. ТЗ: PHP 8.2+, Laravel 11+, Vue 3 или Nuxt 3, SQLite/MySQL/PostgreSQL; 2 часа на реализацию.

## Goals / Non-Goals

**Goals:** минимум шагов запуска; каркас без лишних пакетов; переносимость на MySQL/PostgreSQL.

**Non-Goals:** Docker, CI, деплой, авторизация, SSR.

## Decisions

Структура репозитория:

```
mini-booking/
├── backend/            Laravel 12: app/, database/{migrations,seeders,factories}, routes/api.php, tests/Feature
├── frontend/           Vue 3 + Vite: src/{api,composables,components,utils}, App.vue, main.js
├── openspec/           config.yaml, specs/, changes/
├── .agents/ .claude/   навыки OpenSpec (сгенерированы openspec init)
├── AGENTS.md CLAUDE.md правило spec first
└── README.md
```

- **Laravel 12, не 11 и не 13.** ТЗ допускает 11+. По данным Packagist на 2026-11-16: `laravel/framework` 12.x требует `php ^8.2`, 13.x — `php ^8.3`; ТЗ говорит «PHP 8.2+», значит 13 может не запуститься у проверяющего. 11.x также `^8.2`, но 12 новее.
- **Vue 3 + Vite, а не Nuxt.** Нужна одна страница и JSON API; SSR/роутинг Nuxt не используются. Меньше конфигурации и кода для объяснения на защите.
- **PrimeVue 4.x, а не 5.x.** На 2026-11-16 npm `latest` для `primevue` — 5.0.5, а `v4-stable` — 4.5.5. У 5.0.5 в зависимостях есть `@primeui/license-manager`; что он делает и влияет ли на использование, не проверялось, поэтому берём проверенную ветку 4 (темы — `@primeuix/themes`). Выбор PrimeVue вместо Vuetify/Naive UI/Nuxt UI: готовые `DataTable/Tag/Dialog/ConfirmDialog/Toast/Message`, `ConfirmDialog` закрывает требование «отмена с подтверждением» без самописного кода.
- **SQLite.** Нулевая настройка, воспроизводимость. Ключевая гарантия (unique-индекс) работает одинаково в SQLite, MySQL, PostgreSQL. Ограничение: SQLite сериализует записи, поэтому настоящий параллельный тест на нём не показателен (см. `booking-integrity`).
- **Laravel API без Sanctum.** В Laravel 11+ `routes/api.php` по умолчанию не подключён; `php artisan install:api` дополнительно ставит Sanctum — он не нужен (авторизация вне объёма). Подключаем `api:` в `bootstrap/app.php` вручную (проверить при реализации).
- **JSON-ошибки.** В `bootstrap/app.php` для запросов `api/*` `shouldRenderJsonWhen` возвращает true.
- **Тесты — PHPUnit** (по умолчанию в Laravel, без доп. зависимостей), не Pest.
- **JavaScript вместо TypeScript** на фронте: меньше сборки/типов, быстрее правка на защите.
- **Прокси Vite** `/api` → `http://localhost:8000` вместо CORS.

## Risks / Trade-offs

- Версии пакетов на момент реализации могут уйти вперёд — фиксируем lock-файлами.
- `APP_TIMEZONE=Europe/Moscow` — допущение (ТЗ пояс не называет); вынесено в `.env.example` и README.
