# Design

## Context

Backend создан из скелета `laravel/laravel` 12. Проверено поиском: `view('welcome')` используется только в `routes/web.php`;
`@vite`/Vite нигде в `app/` и `config/` не используется; тесты не обращаются к `/`. Скрипты `composer.json` `setup` и `dev`
вызывают `npm install`, `npm run build` и `npm run dev` (Vite) — без `package.json` они упадут, поэтому правятся вместе с удалением.

## Decisions

1. `routes/web.php`:
   ```php
   // Backend — только JSON API; интерфейс — во frontend/.
   Route::get('/', fn () => response()->json(['name' => 'Mini Booking API', 'slots' => url('/api/slots')]));
   ```
2. `backend/README.md`:
   ```md
   # Mini Booking — backend
   Laravel JSON API. Установка, запуск, тесты и описание решений — в [корневом README](../README.md).
   ```
3. `composer.json`: `setup` — без `npm install` и `npm run build`; `dev` — `php artisan serve` (без `concurrently`, очереди и pail:
   очереди и логи в проекте не используются). `description`: «Mini Booking — JSON API бронирования переговорной комнаты».
   После правки: `composer validate --no-check-publish` (предупреждение о `content-hash` не допускается).
4. `resources/views/` после удаления пуст; Laravel не требует его наличия для JSON API, Git пустой каталог не хранит.

Рассмотрено: оставить страницу приветствия — проверяющий, открыв `http://localhost:8000`, увидел бы стандартную страницу Laravel
вместо понятного ответа; оставить `backend/README.md` — чужой текст в проекте.

## Risks / Trade-offs

- Если в Laravel что-то вызовет `view()` (например, страница ошибки без `Accept: application/json` вне `/api`), фреймворк
  использует свои встроенные шаблоны ошибок из `vendor`, а не `resources/views` — проверить запросом `GET /nope` в браузере.

## Migration Plan

Удаление файлов; откат — revert коммита.
