# Spec Delta

## MODIFIED Requirements

### Requirement: Independent of local timezone setting
Результат тестов MUST NOT зависеть от `BOOKING_TIMEZONE` в `.env` и в окружении и от системного часового пояса: `phpunit.xml` SHALL принудительно задавать `BOOKING_TIMEZONE=Europe/Moscow`, а ожидаемые значения со смещением `+03:00` относятся к этому поясу. Время в тестах MUST фиксироваться в UTC (`Carbon::setTestNow(...->utc())`), как работает приложение.

#### Scenario: Другой пояс в окружении
- **WHEN** `php artisan test` запускается при `BOOKING_TIMEZONE=UTC` или `Asia/Tokyo` и `APP_TIMEZONE=America/Los_Angeles` в окружении
- **THEN** все тесты проходят, как и при значениях по умолчанию
