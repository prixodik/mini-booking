<?php

namespace Tests;

use App\Models\Slot;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Carbon;

abstract class TestCase extends BaseTestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** Момент по времени расписания ('2026-01-05 10:00'), приведённый к UTC, как работает приложение. */
    protected function scheduleTime(string $local): Carbon
    {
        return Carbon::parse($local, config('booking.timezone'))->utc();
    }

    /** Фиксирует «сейчас» по времени расписания; в тестах оно в UTC, иначе now() вернёт московский Carbon. */
    protected function setScheduleNow(string $local): void
    {
        Carbon::setTestNow($this->scheduleTime($local));
    }

    /** Слот, начинающийся в указанное время расписания. */
    protected function slotAt(string $local): Slot
    {
        return Slot::where('starts_at', $this->scheduleTime($local))->firstOrFail();
    }
}
