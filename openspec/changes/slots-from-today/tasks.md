# Tasks

Код не пишется до одобрения change Alex.

## 1. Тест

- [ ] 1.1 `SlotsApiTest`: `test_returns_slots_from_start_of_today_only` — Seeder 5 января 08:00, затем 6 января 08:00, запрос 6 января 10:30: 63 слота, первый `2026-01-06T09:00:00+03:00`, 09:00 и 10:00 — `past`, слотов 5 января нет — сценарий «Прошлые дни не возвращаются»; до правки тест падает (72 слота)

## 2. Реализация

- [ ] 2.1 `Slot::scopeStartingFrom()` по `design.md`
- [ ] 2.2 `SlotController::index`: `->startingFrom(now()->startOfDay())` с комментарием

## 3. Проверка и документация

- [ ] 3.1 `php artisan test` — все тесты зелёные (существующий тест списка слотов: 63 слота, статусы)
- [ ] 3.2 README: в таблице «API» и в «Допущения» — `GET /api/slots` отдаёт слоты с начала сегодняшнего дня; `npx -y @fission-ai/openspec@2.0.0 validate --all --strict`
