# Tasks

Код не пишется до одобрения change Alex.

## 1. Тест и реализация

- [x] 1.1 Тест `test_cancel_with_out_of_range_id_returns_404` в `tests/Feature/BookingApiTest.php`: `DELETE /api/bookings/99999999999999999999` → 404, `message` = «Бронь не найдена.» — сценарий «Слишком большой ID»; убедиться, что до правки тест падает (500) — проверено: до правки 500
- [x] 1.2 `BookingController::destroy(string $id)` с `filter_var(..., FILTER_VALIDATE_INT)` по `design.md`; комментарий в PHPDoc метода

## 2. Проверка

- [x] 2.1 `php artisan test` — все тесты зелёные (включая сценарии «Неизвестный ID», «Повторная отмена»)
- [x] 2.2 `npx -y @fission-ai/openspec@2.0.0 validate --all --strict`
