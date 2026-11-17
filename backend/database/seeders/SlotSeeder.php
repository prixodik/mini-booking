<?php

namespace Database\Seeders;

use App\Models\Slot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Создаёт расписание на N календарных дней (по умолчанию 7), считая сегодня.
 *
 * Идемпотентность: слоты вставляются через INSERT OR IGNORE по уникальному starts_at,
 * поэтому повторный запуск не создаёт дубликатов, не меняет существующие слоты
 * и не трогает бронирования. При запуске в другой день добавляются только недостающие слоты.
 */
class SlotSeeder extends Seeder
{
    public function run(): void
    {
        $timezone = config('booking.timezone');
        $openHour = config('booking.open_hour');
        $closeHour = config('booking.close_hour');
        $minutes = config('booking.slot_minutes');
        $days = config('booking.days_ahead');

        $today = now($timezone)->startOfDay();
        $nowUtc = now()->toDateTimeString();
        $rows = [];

        for ($day = 0; $day < $days; $day++) {
            $date = $today->copy()->addDays($day);
            $start = $date->copy()->setTime($openHour, 0);
            $close = $date->copy()->setTime($closeHour, 0);

            // Слоты текущего дня, которые уже начались, тоже создаются: в API они будут «past».
            while ($start->lessThan($close)) {
                $end = $start->copy()->addMinutes($minutes);
                $rows[] = [
                    'starts_at' => $start->copy()->utc()->toDateTimeString(),
                    'ends_at' => $end->copy()->utc()->toDateTimeString(),
                    'created_at' => $nowUtc,
                    'updated_at' => $nowUtc,
                ];
                $start = $end;
            }
        }

        Slot::query()->insertOrIgnore($rows);
    }
}
