# Spec Delta

## MODIFIED Requirements

### Requirement: Environment configuration
`backend/.env.example` SHALL задавать `BOOKING_TIMEZONE` явно (по умолчанию `Europe/Moscow`), `DB_CONNECTION=sqlite` и `APP_LOCALE=ru`. Время расписания MUST определяться значением `config('booking.timezone')`. `config('app.timezone')` MUST быть зафиксирован значением `UTC` в `config/app.php` и не зависеть от переменных окружения.

#### Scenario: Часовой пояс задан явно
- **WHEN** разработчик копирует `.env.example` в `.env`
- **THEN** `config('booking.timezone')` возвращает `Europe/Moscow`, `config('app.timezone')` возвращает `UTC`, а изменение `BOOKING_TIMEZONE` меняет пояс расписания

#### Scenario: Старый APP_TIMEZONE в .env
- **WHEN** в `.env` остался `APP_TIMEZONE=Europe/Moscow`
- **THEN** `config('app.timezone')` всё равно равен `UTC`, и сохранённые слоты не читаются со сдвигом
