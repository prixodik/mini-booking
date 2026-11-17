<?php

namespace Database\Factories;

use App\Models\Slot;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/** @extends Factory<Slot> */
class SlotFactory extends Factory
{
    protected $model = Slot::class;

    public function definition(): array
    {
        $start = Carbon::tomorrow(config('booking.timezone'))->setTime(9, 0)->utc();

        return [
            'starts_at' => $start->copy()->utc(),
            'ends_at' => $start->copy()->addHour()->utc(),
        ];
    }

    public function startingAt(Carbon $start): static
    {
        return $this->state(fn () => [
            'starts_at' => $start->copy()->utc(),
            'ends_at' => $start->copy()->addHour()->utc(),
        ]);
    }
}
