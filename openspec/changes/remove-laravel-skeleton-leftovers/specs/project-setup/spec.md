# Spec Delta

## ADDED Requirements

### Requirement: Backend contains only the JSON API
Каталог `backend/` MUST NOT содержать файлы скелета `laravel/laravel`, не относящиеся к JSON API: workflow GitHub, журнал изменений фреймворка, конфигурацию StyleCI, Vite-обвязку (`package.json`, `vite.config.js`, `resources/css`, `resources/js`) и Blade-страницу приветствия. `backend/README.md` SHALL ссылаться на корневой `README.md`. Запрос `GET /` к backend SHALL отвечать JSON, а не HTML-страницей.

#### Scenario: Состав каталога
- **WHEN** выполняется `git ls-files backend` 
- **THEN** в списке нет `.github/`, `CHANGELOG.md`, `.styleci.yml`, `package.json`, `vite.config.js`, `resources/css/`, `resources/js/`, `resources/views/welcome.blade.php`

#### Scenario: Корень backend
- **WHEN** выполняется `GET /` к `php artisan serve`
- **THEN** ответ 200 с JSON, содержащим название API и путь `/api/slots`

#### Scenario: Чистый запуск
- **WHEN** на свежем клоне выполняются команды backend из корневого `README.md` и `php artisan test`
- **THEN** все команды проходят без ошибок, `composer validate` не сообщает о рассинхронизации `composer.lock`
