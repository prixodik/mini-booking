# AGENTS.md

## Правило проекта: spec first

Работа над проектом ведётся только через спецификацию (OpenSpec):

1. **Сначала спецификация.** Любое изменение поведения начинается с change в `openspec/changes/<имя>/`
   (`proposal.md`, `specs/*/spec.md`, `design.md`, `tasks.md`).
2. **Проверка.** `npx @fission-ai/openspec@2.0.0 validate --all --strict` должен проходить; спецификация
   сверяется с ТЗ (см. `openspec/config.yaml`, поле `context`).
3. **Согласование.** Владелец проекта (Alex) одобряет change. До одобрения прикладной код
   (`backend/`, `frontend/`) не пишется и фреймворки не устанавливаются.
4. **Код.** Реализация идёт строго по `tasks.md`; отклонение от спецификации — сначала правка спецификации.
5. **Архив.** После реализации и проверки change архивируется (`openspec archive <имя>`), дельты
   переезжают в `openspec/specs/`.

## Команды OpenSpec

OpenSpec 2.x требует Node.js >= 22.12. Инструмент можно не ставить глобально:

```bash
npx -y @fission-ai/openspec@2.0.0 list            # активные changes
npx -y @fission-ai/openspec@2.0.0 validate --all  # проверка формата спецификаций
npx -y @fission-ai/openspec@2.0.0 show <change>   # просмотр change
```

Навыки для Claude Code лежат в `.agents/skills/` (ссылки из `.claude/skills/`):
`/openspec-propose`, `/openspec-apply-change`, `/openspec-explore`, `/openspec-archive-change`,
`/openspec-sync-specs`, `/openspec-update-change`.

## Порядок реализации changes

1. `scaffold-project`
2. `add-data-model-and-seeding`
3. `add-slots-api`
4. `add-bookings-api`
5. `add-booking-frontend`
6. `add-tests-and-readme`

Контекст проекта и ограничения из ТЗ — в `openspec/config.yaml`.
