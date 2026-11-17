# Spec Delta

## MODIFIED Requirements

### Requirement: Environment settings
README MUST описывать ключевые настройки `.env`: `BOOKING_TIMEZONE`, `DB_CONNECTION` и путь SQLite-файла, `APP_LOCALE`. README MUST указывать, что `app.timezone` зафиксирован в UTC, а смена `BOOKING_TIMEZONE` на заполненной БД не поддерживается: Seeder откажется добавлять слоты, нужно выполнить `php artisan migrate:fresh --seed`.

#### Scenario: Смена часового пояса
- **WHEN** читатель хочет изменить пояс расписания
- **THEN** README указывает `BOOKING_TIMEZONE`, предупреждает, что на заполненной БД Seeder завершится ошибкой, и даёт команду `php artisan migrate:fresh --seed`

### Requirement: Time handling explanation
README SHALL объяснять, каким временем оперирует приложение: в БД и в `app.timezone` — UTC, пояс расписания — `config('booking.timezone')`, ISO 8601 со смещением в API, `meta.timezone` для frontend, правило «прошедшего» слота (`starts_at <= now`). README MUST пояснять соответствие ТЗ («часовой пояс из конфигурации Laravel»): пояс расписания задаётся в конфигурации Laravel (`config/booking.php`, `BOOKING_TIMEZONE`), а `app.timezone` намеренно равен UTC, чтобы все даты в БД были в одном поясе.

#### Scenario: Вопрос о времени
- **WHEN** проверяющий ищет, в каком поясе показываются слоты и хранятся данные
- **THEN** README называет пояс расписания, формат API, правило прошедшего слота и объясняет, почему `app.timezone` равен UTC
