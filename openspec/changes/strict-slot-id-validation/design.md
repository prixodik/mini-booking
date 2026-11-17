# Design

## Context

Laravel 12.72 (`vendor/.../ValidatesAttributes.php`, `validateInteger`): без параметра правило проверяет
`filter_var($value, FILTER_VALIDATE_INT) !== false`, и `true` превращается в `1`. С параметром `strict` — `is_int($value)`.
Правило `exists:slots,id` выполняется после и при `true` ищет слот 1.

## Decisions

`'slot_id' => ['required', 'integer:strict', 'exists:slots,id']`. Сообщение берётся из уже существующего ключа `'integer'`
в `messages()` («Поле «слот» должно быть целым числом.»), потому что имя правила то же.

Следствие: строка `"5"` тоже получает 422. Это контракт JSON API: frontend отправляет число. Тест
`test_validation_errors_are_returned_per_field_as_json` отправляет пустую форму без `slot_id` — на него правка не влияет.

Рассмотрено:
- `numeric` + `exists`: `true` отклоняется, но `"5"` и `5.0` проходят, а `5.5` дойдёт до `exists`, где 422 будет с сообщением
  «не существует» — менее точно.
- Оставить `integer` и добавить `'not_in:true,false'` или замыкание — больше кода и труднее объяснить, чем один параметр правила.

## Risks / Trade-offs

Клиенты, отправляющие форму (`application/x-www-form-urlencoded`), где все значения строки, получат 422. Таких клиентов в проекте нет.

## Migration Plan

Только backend; откат — revert коммита. Архивировать после `add-bookings-api` (дельта `MODIFIED`).
