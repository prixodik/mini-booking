# Tasks

Код не пишется до одобрения change Alex.

- [x] 1.1 `git rm` файлов из `proposal.md` («Удаляются»)
- [x] 1.2 `routes/web.php`: JSON для `GET /` по `design.md`; `backend/README.md` — ссылка на корневой README
- [x] 1.3 `composer.json`: скрипты `setup`/`dev` без npm, `description`; `composer validate --no-check-publish` — сценарий «Чистый запуск»
- [ ] 2.1 `php artisan test` — все зелёные; `php artisan serve`: `GET /` — JSON, `GET /api/slots` — 200, `GET /nope` в браузере — страница 404 без ошибки — сценарий «Корень backend»
- [ ] 2.2 Свежий клон во временный каталог: команды backend из корневого README проходят; `git ls-files backend` — сценарий «Состав каталога»; `npx -y @fission-ai/openspec@2.0.0 validate --all --strict`
