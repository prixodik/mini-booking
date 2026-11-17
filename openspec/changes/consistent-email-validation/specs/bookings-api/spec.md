# Spec Delta

## ADDED Requirements

### Requirement: Email format matches the browser check
Сервер MUST отклонять `customer_email` без точки в доменной части или с пробелами (то же правило формата, что проверяет браузер: `^[^\s@]+@[^\s@]+\.[^\s@]+$`) в дополнение к проверке Laravel `email`. Ответ — 422 с ошибкой в `errors.customer_email`. Адреса с кириллицей в имени или домене, проходящие обе проверки, MUST приниматься.

#### Scenario: Домен без точки
- **WHEN** `customer_email` равен `a@b` или `a@localhost`
- **THEN** ответ 422 с ошибкой в `errors.customer_email`, бронь не создаётся

#### Scenario: Кириллический адрес
- **WHEN** `customer_email` равен `иван@пример.рф`, а слот свободен и в будущем
- **THEN** ответ 201
