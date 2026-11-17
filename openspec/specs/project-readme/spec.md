# project-readme Specification

## Purpose
Определяет содержание README, достаточное для воспроизводимого запуска и защиты решения.

## Requirements

### Requirement: Setup and run instructions
`README.md` SHALL перечислять зависимости (PHP 8.2+, Composer, Node.js, версии из `package.json`) и содержать копируемые команды для: установки backend и frontend, создания `.env` и ключа, миграций, заполнения БД Seeder, запуска backend, запуска frontend, запуска тестов.

#### Scenario: Запуск с нуля
- **WHEN** разработчик выполняет команды README последовательно на чистом клоне
- **THEN** backend доступен на `http://localhost:8000`, frontend на адресе Nuxt dev-сервера, расписание заполнено

### Requirement: Installation troubleshooting
README SHALL описывать известные проблемы установки: долгий `composer install` без GitHub-токена (предупреждения «Could not authenticate against github.com», пакеты ставятся из исходников) с указанием, как задать токен (`composer config -g github-oauth.github.com <token>`), и ошибку «Filename too long» на Windows при длинном пути к проекту (короткий путь или `git config --global core.longpaths true`).

#### Scenario: Медленная установка
- **WHEN** разработчик видит сотню предупреждений «Could not authenticate against github.com» при `composer install`
- **THEN** README объясняет причину и как задать токен

### Requirement: Environment settings
README MUST описывать ключевые настройки `.env`: `BOOKING_TIMEZONE`, `DB_CONNECTION` и путь SQLite-файла, `APP_LOCALE`. README MUST указывать, что `app.timezone` зафиксирован в UTC, а смена `BOOKING_TIMEZONE` на заполненной БД не поддерживается: Seeder откажется добавлять слоты, нужно выполнить `php artisan migrate:fresh --seed`.

#### Scenario: Смена часового пояса
- **WHEN** читатель хочет изменить пояс расписания
- **THEN** README указывает `BOOKING_TIMEZONE`, предупреждает, что на заполненной БД Seeder завершится ошибкой, и даёт команду `php artisan migrate:fresh --seed`

### Requirement: Uniqueness explanation
README SHALL кратко объяснять, как обеспечена уникальность брони: поле `active_slot_id` с UNIQUE-индексом, мягкая отмена, перехват нарушения уникальности и ответ 409, причина отказа от «проверки перед вставкой».

#### Scenario: Вопрос на защите
- **WHEN** проверяющий читает раздел об уникальности
- **THEN** он находит механизм, место в коде (миграция и сервис) и ограничения проверки на SQLite

### Requirement: Time handling explanation
README SHALL объяснять, каким временем оперирует приложение: в БД и в `app.timezone` — UTC, пояс расписания — `config('booking.timezone')`, ISO 8601 со смещением в API, `meta.timezone` для frontend, правило «прошедшего» слота (`starts_at <= now`). README MUST пояснять соответствие ТЗ («часовой пояс из конфигурации Laravel»): пояс расписания задаётся в конфигурации Laravel (`config/booking.php`, `BOOKING_TIMEZONE`), а `app.timezone` намеренно равен UTC, чтобы все даты в БД были в одном поясе.

#### Scenario: Вопрос о времени
- **WHEN** проверяющий ищет, в каком поясе показываются слоты и хранятся данные
- **THEN** README называет пояс расписания, формат API, правило прошедшего слота и объясняет, почему `app.timezone` равен UTC

### Requirement: Honest status and assumptions
README MUST содержать раздел о допущениях и незавершённом: что не успели, что вне объёма (авторизация, Scheduler/cron), допущения (отмена без авторизации, отмена брони прошедшего слота, список всех слотов без пагинации, настоящий параллельный тест невозможен на SQLite).

#### Scenario: Часть работы не завершена
- **WHEN** на момент сдачи что-то не реализовано
- **THEN** README перечисляет это явно, не заявляя функцию готовой
