<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;

/** Слот занят или уже прошёл: API отвечает 409 с понятным сообщением. */
class SlotUnavailableException extends RuntimeException
{
    public static function taken(): self
    {
        return new self('Этот слот уже занят.');
    }

    public static function past(): self
    {
        return new self('Этот слот уже прошёл.');
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 409);
    }
}
