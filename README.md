# Mini Booking

Бронирование одной переговорной комнаты по часовым слотам (тестовое задание: Laravel + Vue 3).

> **Статус:** этап спецификации. Реализация начнётся после согласования changes в `openspec/changes/`.
> Полный README (установка, запуск, тесты, уникальность брони, время, ограничения) появится в change `add-tests-and-readme`.

## Структура репозитория

```
backend/     Laravel 12 — JSON API, миграции, Seeder, тесты (пока не создан)
frontend/    Nuxt 3 + PrimeVue 4 — одна страница (пока не создан)
openspec/    спецификации и changes (spec-driven разработка)
AGENTS.md    правило «spec first» и порядок реализации
```

## Процесс разработки (OpenSpec)

Работаем по схеме spec first: сначала change со спецификацией, проверка и согласование, затем код по `tasks.md`.
OpenSpec 2.x требует Node.js >= 22.12.

```bash
npx -y @fission-ai/openspec@2.0.0 list             # активные changes
npx -y @fission-ai/openspec@2.0.0 validate --all   # проверка спецификаций
npx -y @fission-ai/openspec@2.0.0 show <change>    # просмотр change
```

Порядок реализации: `scaffold-project` → `add-data-model-and-seeding` → `add-slots-api` →
`add-bookings-api` → `add-booking-frontend` → `add-tests-and-readme`.
Соответствие ТЗ и спецификаций: [`openspec/traceability.md`](openspec/traceability.md).
