<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;

/** Отмена невозможна: брони нет или она уже отменена. Оба случая — 404, различаются сообщением. */
class BookingNotFoundException extends RuntimeException
{
    public static function unknown(): self
    {
        return new self('Бронь не найдена.');
    }

    public static function alreadyCancelled(): self
    {
        return new self('Бронь уже отменена.');
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 404);
    }
}
