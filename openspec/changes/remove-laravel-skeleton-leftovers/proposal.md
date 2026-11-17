# Proposal

## Why

Ревью (`review-mini-booking.md`, Ж-8): в `backend/` остались файлы скелета `laravel/laravel`, которые проекту не нужны и вводят
проверяющего в заблуждение: `backend/README.md` с логотипом и текстом Laravel, `CHANGELOG.md` фреймворка, четыре workflow
репозитория Laravel в `backend/.github/` (GitHub их из подпапки не запускает), Vite-обвязка (`package.json`, `vite.config.js`,
`resources/css`, `resources/js`) и страница `welcome.blade.php` для маршрута `/`. Backend — только JSON API (ТЗ, п. 1, 3), вёрстка
живёт во `frontend/`. Оценивается читаемость и воспроизводимость (ТЗ, «При оценке учитываются…»). Alex: убрать мусор.

## What Changes

- Удаляются: `backend/.github/` (4 файла), `backend/CHANGELOG.md`, `backend/.styleci.yml`, `backend/package.json`,
  `backend/vite.config.js`, `backend/resources/css/app.css`, `backend/resources/js/app.js`, `backend/resources/js/bootstrap.js`,
  `backend/resources/views/welcome.blade.php`.
- `backend/README.md` заменяется двумя строками со ссылкой на корневой `README.md`.
- `routes/web.php`: `GET /` вместо страницы приветствия отвечает JSON с названием API и ссылкой на `/api/slots`.
- `composer.json`: из скриптов `setup` и `dev` убираются вызовы `npm`/Vite (`dev` — только `php artisan serve`), `description`
  меняется на описание проекта. Поле `name` (`laravel/laravel`) не меняется: оно входит в `content-hash` `composer.lock`,
  и смена потребовала бы `composer update --lock`.

Остаются (нужны или безвредны): `.editorconfig`, `.gitattributes`, `.gitignore`, `artisan`, `public/*`, `routes/console.php`,
пустой каталог `resources/views` не хранится.

Вне объёма: удаление неиспользуемых конфигов (`config/mail.php`, `config/queue.php` и т. п. — их ждёт фреймворк), изменения API,
frontend и других пунктов ревью.

## Capabilities

### New Capabilities

### Modified Capabilities
- `project-setup`: в `backend/` нет файлов скелета Laravel, не относящихся к JSON API (новое требование).

## Impact

Удаление файлов в `backend/`, правка `backend/routes/web.php`, `backend/composer.json`, `backend/README.md`. Корневой `README.md`
команды не меняет (`composer install`, `php artisan …` работают как прежде).

## Decisions of Alex

- Убрать мусор из скелета Laravel (Ж-8 из отчёта ревью, 17.11.2026).

## Open Questions

Нет: нужно только одобрение change.
