# Design

## Context

`SlotController::index`: `Slot::with('activeBooking')->orderBy('starts_at')->get()`. Слоты хранятся в UTC (`App\Models\Slot`),
а `now()` возвращает время в `app.timezone`. Если сравнить `starts_at` с Carbon напрямую, Laravel подставит время в поясе приложения
и ошибётся на величину смещения (замечание ревью И-5), поэтому граница переводится в UTC явно.

## Decisions

1. Локальный scope в модели:
   ```php
   /** Слоты, начинающиеся не раньше $moment. starts_at хранится в UTC, поэтому момент переводится через toDbValue(). */
   public function scopeStartingFrom(Builder $query, \DateTimeInterface $moment): Builder
   {
       return $query->where('starts_at', '>=', self::toDbValue($moment));
   }
   ```
2. Контроллер:
   ```php
   // С начала сегодняшнего дня по времени расписания: прошедшие слоты сегодня нужны (ТЗ), прошлые дни — нет.
   $slots = Slot::with('activeBooking')
       ->startingFrom(now()->startOfDay())
       ->orderBy('starts_at')
       ->get();
   ```
   `now()` уже в `app.timezone`, поэтому `startOfDay()` — 00:00 по времени расписания; `toDbValue` переводит его в UTC
   (для Москвы — 21:00 предыдущего дня по UTC).
3. Сравнение строк `Y-m-d H:i:s` в SQLite корректно, потому что все значения записаны в одном формате (`Slot::DB_FORMAT`) и в UTC;
   в MySQL/PostgreSQL колонка `dateTime` сравнивается как дата.

Рассмотрено: фильтровать даты на клиенте (как было в первой версии `use-primevue-date-filter-and-tags`) — API продолжал бы
отдавать растущую историю, и правило «что показывать» жило бы в двух местах. Удалять старые слоты Seeder'ом — противоречит ТЗ
(«ранее созданные данные сохраняются»).

## Risks / Trade-offs

- Брони прошлых дней больше не видны через API; отменить их через интерфейс и раньше было нельзя (кнопка скрыта у `past`),
  а `DELETE /api/bookings/{id}` по известному ID работает как прежде.

## Migration Plan

Backend и README; схема БД не меняется. Архивировать после `add-slots-api` (дельта `MODIFIED`).
