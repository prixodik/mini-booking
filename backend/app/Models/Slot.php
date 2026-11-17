<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Часовой слот переговорной.
 *
 * starts_at/ends_at хранятся в БД в UTC, как и все остальные даты (config('app.timezone') = UTC),
 * поэтому обычный where('starts_at', '>', now()) работает верно. Пояс расписания (config('booking.timezone'))
 * нужен для расчёта расписания и вывода времени клиенту.
 */
class Slot extends Model
{
    use HasFactory;

    protected $fillable = ['starts_at', 'ends_at'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    /** Приводит даты к UTC перед сохранением через модель. */
    public function fromDateTime($value)
    {
        return parent::fromDateTime(empty($value) ? $value : $this->asDateTime($value)->copy()->utc());
    }

    /** Слоты, начинающиеся не раньше $moment. Момент может быть в любом поясе: в запрос он уходит в UTC. */
    public function scopeStartingFrom(Builder $query, CarbonInterface $moment): Builder
    {
        return $query->where('starts_at', '>=', $moment->copy()->utc());
    }

    /** Слот прошёл, если он начался в текущий момент или раньше. */
    public function isPast(): bool
    {
        return $this->starts_at->lessThanOrEqualTo(now());
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /** Активная бронь: у неё active_slot_id = id слота; UNIQUE-индекс гарантирует, что такая одна. */
    public function activeBooking(): HasOne
    {
        return $this->hasOne(Booking::class, 'active_slot_id');
    }
}
