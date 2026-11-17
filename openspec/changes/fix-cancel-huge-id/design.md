# Design

## Context

`routes/api.php`: `Route::delete('/bookings/{id}', ...)->whereNumber('id')` (маска `[0-9]+`). Laravel передаёт параметр маршрута
строкой; при сигнатуре `destroy(int $id)` PHP в нестрогом режиме приводит `"99999999999999999999"` к `int` и не может (переполнение) —
`TypeError`, ответ 500. Проверено запросом во время ревью.

## Decisions

Принимать строку и проверять её явно:

```php
public function destroy(string $id): Response
{
    // ID из цифр, но больше PHP_INT_MAX: такой брони быть не может.
    $bookingId = filter_var($id, FILTER_VALIDATE_INT);
    if ($bookingId === false) {
        throw BookingNotFoundException::unknown();
    }

    $this->bookings->cancel($bookingId);

    return response()->noContent();
}
```

`whereNumber('id')` остаётся: нецифровые ID (`/api/bookings/abc`) по-прежнему не совпадают с маршрутом и получают стандартный 404 Laravel.

Рассмотренные варианты:

- **Маска `[0-9]{1,18}` в маршруте.** Одна строка, но слишком длинный ID получит 404 «маршрут не найден» с другим сообщением,
  а спецификация требует «Бронь не найдена».
- **Route model binding (`Booking $booking`).** Идиоматично, но 404 для неизвестного ID пришлось бы настраивать через `->missing(...)`,
  а различие «не найдена / уже отменена» всё равно осталось бы в сервисе — больше движущихся частей ради одного граничного случая.
- **`(int) $id`.** Работает (PHP «насыщает» до `PHP_INT_MAX`, брони с таким ID нет), но поведение неочевидно и трудно объяснить на защите.

## Risks / Trade-offs

- `filter_var` вернёт `false` и для `"0"`? Нет: `FILTER_VALIDATE_INT` принимает `0` (результат `0`, проверка строгая `=== false`), брони с ID 0 нет → 404 «Бронь не найдена», как и сейчас.
- Ведущие нули (`/api/bookings/007`) `FILTER_VALIDATE_INT` отклоняет → 404 «Бронь не найдена». Сейчас такой запрос отменил бы бронь 7;
  клиент такие ID не формирует, поведение допустимо.

## Migration Plan

Только backend; откат — revert коммита. Архивировать после `add-bookings-api` (дельта `MODIFIED`).
