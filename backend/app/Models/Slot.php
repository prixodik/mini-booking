<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * Часовой слот переговорной.
 *
 * Время хранится в БД в UTC, а наружу отдаётся в часовом поясе расписания
 * (config('app.timezone')). Поэтому смена APP_TIMEZONE не портит сохранённые слоты.
 */
class Slot extends Model
{
    use HasFactory;

    public const DB_FORMAT = 'Y-m-d H:i:s';

    protected $fillable = ['starts_at', 'ends_at'];

    public static function toDbValue(\DateTimeInterface|string $moment): string
    {
        return Carbon::parse($moment)->utc()->format(self::DB_FORMAT);
    }

    protected function startsAt(): Attribute
    {
        return $this->utcAttribute();
    }

    protected function endsAt(): Attribute
    {
        return $this->utcAttribute();
    }

    private function utcAttribute(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => Carbon::parse($value, 'UTC')->setTimezone(config('app.timezone')),
            set: fn ($value) => self::toDbValue($value),
        );
    }

    /** Слоты, начинающиеся не раньше $moment. starts_at хранится в UTC, поэтому момент переводится через toDbValue(). */
    public function scopeStartingFrom(Builder $query, \DateTimeInterface $moment): Builder
    {
        return $query->where('starts_at', '>=', self::toDbValue($moment));
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
