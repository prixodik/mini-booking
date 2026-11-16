# Spec Delta

## Purpose
Определяет содержание README, достаточное для воспроизводимого запуска и защиты решения.

## ADDED Requirements

### Requirement: Setup and run instructions
`README.md` SHALL перечислять зависимости (PHP 8.2+, Composer, Node.js, версии из `package.json`) и содержать копируемые команды для: установки backend и frontend, создания `.env` и ключа, миграций, заполнения БД Seeder, запуска backend, запуска frontend, запуска тестов.

#### Scenario: Запуск с нуля
- **WHEN** разработчик выполняет команды README последовательно на чистом клоне
- **THEN** backend доступен на `http://localhost:8000`, frontend на адресе Vite, расписание заполнено

### Requirement: Environment settings
README MUST описывать ключевые настройки `.env`: `APP_TIMEZONE`, `DB_CONNECTION` и путь SQLite-файла, `APP_LOCALE`.

#### Scenario: Смена часового пояса
- **WHEN** читатель хочет изменить пояс расписания
- **THEN** README указывает `APP_TIMEZONE` и что Seeder нужно запустить заново для недостающих слотов

### Requirement: Uniqueness explanation
README SHALL кратко объяснять, как обеспечена уникальность брони: поле `active_slot_id` с UNIQUE-индексом, мягкая отмена, перехват нарушения уникальности и ответ 409, причина отказа от «проверки перед вставкой».

#### Scenario: Вопрос на защите
- **WHEN** проверяющий читает раздел об уникальности
- **THEN** он находит механизм, место в коде (миграция и сервис) и ограничения проверки на SQLite

### Requirement: Time handling explanation
README SHALL объяснять, каким временем оперирует приложение: расписание по `app.timezone`, хранение в UTC, ISO 8601 со смещением в API, `meta.timezone` для frontend, правило «прошедшего» слота (`starts_at <= now`).

#### Scenario: Вопрос о времени
- **WHEN** проверяющий ищет, в каком поясе показываются слоты
- **THEN** README называет пояс расписания, формат API и правило прошедшего слота

### Requirement: Honest status and assumptions
README MUST содержать раздел о допущениях и незавершённом: что не успели, что вне объёма (авторизация, Scheduler/cron), допущения (отмена без авторизации, отмена брони прошедшего слота, список всех слотов без пагинации, настоящий параллельный тест невозможен на SQLite).

#### Scenario: Часть работы не завершена
- **WHEN** на момент сдачи что-то не реализовано
- **THEN** README перечисляет это явно, не заявляя функцию готовой
