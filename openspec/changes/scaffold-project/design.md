# Design

## Context

Репозиторий пуст. ТЗ: PHP 8.2+, Laravel 11+, Vue 3 или Nuxt 3 (владелец выбрал Nuxt 3), SQLite/MySQL/PostgreSQL; 2 часа на реализацию.

## Goals / Non-Goals

**Goals:** минимум шагов запуска; каркас без лишних пакетов; переносимость на MySQL/PostgreSQL.

**Non-Goals:** Docker, CI, деплой, авторизация, SSR.

## Decisions

Структура репозитория:

```
mini-booking/
├── backend/            Laravel 12: app/, database/{migrations,seeders,factories}, routes/api.php, tests/Feature
├── frontend/           Nuxt 3 (SPA): app.vue, layouts/, pages/, components/, composables/, services/, utils/, plugins/, assets/, nuxt.config.js (подробно — в `add-booking-frontend`)
├── openspec/           config.yaml, specs/, changes/
├── .agents/ .claude/   навыки OpenSpec (сгенерированы openspec init)
├── AGENTS.md CLAUDE.md правило spec first
└── README.md
```

- **Laravel 12, не 11 и не 13.** ТЗ допускает 11+. По данным Packagist на 2026-11-16: `laravel/framework` 12.x требует `php ^8.2`, 13.x — `php ^8.3`; ТЗ говорит «PHP 8.2+», значит 13 может не запуститься у проверяющего. 11.x также `^8.2`, но 12 новее.
- **Nuxt 3 в режиме SPA (`ssr: false`).** Выбор владельца (ТЗ допускает «Vue 3 или Nuxt 3»). По npm, ветка 3.x на 2026-11-16 — `nuxt@3.21.11` (dist-tag `3x`), `latest` — 4.x; ставим `nuxt@3`. SSR отключаем: страница полностью зависит от клиентских запросов и часового пояса расписания, SSR добавил бы риск расхождения гидратации без пользы. Файловый роутинг (`pages/`) используется по решению владельца: структура должна быть правильной и расширяемой, даже если по ТЗ нужна одна страница. Сейчас есть один маршрут `/` (`pages/index.vue`); новые страницы добавляются файлами в `pages/` без переделки каркаса. Автоимпорт Nuxt применяется к `composables/`, `utils/` и `components/`.
- **PrimeVue подключается плагином, без `@primevue/nuxt-module`.** В npm у модуля единственный dist-tag — `latest` = 5.0.5 (тянет PrimeVue 5), версию для PrimeVue 4 я не проверял; плагин `plugins/primevue.js` (`app.use(PrimeVue, {theme})`) — несколько строк и не зависит от этого.
- **PrimeVue 4.x, а не 5.x.** На 2026-11-16 npm `latest` для `primevue` — 5.0.5, а `v4-stable` — 4.5.5. У 5.0.5 в зависимостях есть `@primeui/license-manager`; что он делает и влияет ли на использование, не проверялось, поэтому берём проверенную ветку 4 (темы — `@primeuix/themes`). Выбор PrimeVue вместо Vuetify/Naive UI/Nuxt UI: готовые `DataTable/Tag/Dialog/ConfirmDialog/Toast/Message`, `ConfirmDialog` закрывает требование «отмена с подтверждением» без самописного кода.
- **SQLite.** Нулевая настройка, воспроизводимость. Ключевая гарантия (unique-индекс) работает одинаково в SQLite, MySQL, PostgreSQL. Ограничение: SQLite сериализует записи, поэтому настоящий параллельный тест на нём не показателен (см. `booking-integrity`).
- **Laravel API без Sanctum.** В Laravel 11+ `routes/api.php` по умолчанию не подключён; `php artisan install:api` дополнительно ставит Sanctum — он не нужен (авторизация вне объёма). Подключаем `api:` в `bootstrap/app.php` вручную (проверить при реализации).
- **JSON-ошибки.** В `bootstrap/app.php` для запросов `api/*` `shouldRenderJsonWhen` возвращает true.
- **Тесты — PHPUnit** (по умолчанию в Laravel, без доп. зависимостей), не Pest.
- **JavaScript вместо TypeScript** на фронте (Nuxt допускает JS): меньше сборки/типов, быстрее правка на защите.
- **Прокси Nuxt (Nitro `devProxy`)** `/api` → `http://localhost:8000/api` вместо CORS; в `nuxt.config.js` (синтаксис проверить по документации Nuxt при реализации).

## Risks / Trade-offs

- Версии пакетов на момент реализации могут уйти вперёд — фиксируем lock-файлами.
- `APP_TIMEZONE=Europe/Moscow` — допущение (ТЗ пояс не называет); вынесено в `.env.example` и README.
