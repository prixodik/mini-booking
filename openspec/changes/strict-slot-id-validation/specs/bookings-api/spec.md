# Spec Delta

## MODIFIED Requirements

### Requirement: Validation
Запрос MUST валидироваться средствами Laravel (FormRequest): `slot_id` обязателен, целое число JSON (не логическое значение, не строка и не дробное число) и существует в `slots`; `customer_name` обязателен, строка, не длиннее 255 символов; `customer_email` обязателен, корректный email, не длиннее 255 символов. При ошибке SHALL возвращаться 422 и стандартный JSON Laravel `{"message": "...", "errors": {"<поле>": ["..."]}}`; бронь не создаётся.

#### Scenario: Пустые поля
- **WHEN** тело запроса пустое
- **THEN** ответ 422 содержит ошибки для `slot_id`, `customer_name` и `customer_email`

#### Scenario: Несуществующий slot_id
- **WHEN** `slot_id` равен 999999
- **THEN** ответ 422 содержит ошибку в `errors.slot_id`, а не 404 и не 409

#### Scenario: Некорректный email
- **WHEN** `customer_email` равен `not-an-email`
- **THEN** ответ 422 содержит ошибку в `errors.customer_email`

#### Scenario: Логическое значение вместо ID
- **WHEN** `slot_id` равен `true`
- **THEN** ответ 422 содержит ошибку в `errors.slot_id`, бронь не создаётся

#### Scenario: Строка вместо числа
- **WHEN** `slot_id` равен строке `"5"`
- **THEN** ответ 422 содержит ошибку в `errors.slot_id`
