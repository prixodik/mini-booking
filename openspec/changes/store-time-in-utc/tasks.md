# Tasks

Код не пишется до одобрения change Alex (см. `proposal.md`, Open Questions).

## 1. Решения

- [ ] 1.1 Подтверждение Alex: `app.timezone` захардкожен в `UTC` (рекомендуется); защита записи слотов — пометка в PHPDoc без setter (рекомендуется)

## 2. Подготовка тестов (до правки кода)

- [ ] 2.1 `Tests\TestCase`: помощники `freezeTime()` и `slotAt()`, сброс `setTestNow` в `tearDown`; перевести существующие тесты на них (на старом коде они зелёные)
- [ ] 2.2 Новые тесты по `design.md` п. 6: естественный `where` по `starts_at`, хранение в UTC (слот и `created_at`), отказ сидера после смены пояса — сценарии «Естественный запрос по времени», «Слот 09:00 по времени расписания», «Служебные поля в UTC», «Смена пояса на заполненной базе»; до правки кода падают (проверить)

## 3. Реализация

- [ ] 3.1 `config/app.php`: `'timezone' => 'UTC'`; `config/booking.php`: `timezone`; `.env.example`: `BOOKING_TIMEZONE`; `phpunit.xml`: `BOOKING_TIMEZONE` вместо `APP_TIMEZONE` — сценарии «Часовой пояс расписания задан явно», «Старый APP_TIMEZONE в .env»
- [ ] 3.2 `Slot`: cast вместо аксессоров, scope `startingFrom()` с `->utc()`, удалить `toDbValue()`/`DB_FORMAT`; PHPDoc про запись в UTC
- [ ] 3.3 `SlotResource`, `SlotController` (`meta`, начало «сегодня»), `SlotFactory`: пояс из `config('booking.timezone')`
- [ ] 3.4 `SlotSeeder`: пояс из `booking.timezone`, явный перевод в UTC, защита `assertScheduleMatchesTimezone()` — сценарии «Тот же пояс», «Пустая база»

## 4. Проверка и документы

- [ ] 4.1 `php artisan test` — все зелёные; запуск с `BOOKING_TIMEZONE=Asia/Tokyo` и `APP_TIMEZONE=America/Los_Angeles` в окружении — тоже зелёные — сценарий «Другой пояс в окружении»
- [ ] 4.2 Ручная проверка на временной БД: `migrate`, `db:seed` дважды (63 слота); `BOOKING_TIMEZONE=Asia/Novosibirsk php artisan db:seed` — ошибка с подсказкой, слотов по-прежнему 63; `migrate:fresh --seed` с новым поясом — 63 слота; `GET /api/slots` — смещение `+03:00` и `meta.timezone`
- [ ] 4.3 README (таблица настроек, смена пояса, «Каким временем оперирует приложение», тесты), `openspec/config.yaml`, `openspec/traceability.md`; в проверке браузером frontend показывает прежние времена
- [ ] 4.4 Локальный `backend/.env` (не в Git): `BOOKING_TIMEZONE`, затем `migrate:fresh --seed`; `npx -y @fission-ai/openspec@2.0.0 validate --all --strict`
