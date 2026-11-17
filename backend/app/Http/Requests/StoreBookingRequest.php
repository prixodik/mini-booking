<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    // Тот же формат, что проверяет форма (frontend/components/booking/BookingDialog.vue, EMAIL_RE): точка в домене, без пробелов.
    private const EMAIL_FORMAT = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/u';

    public function authorize(): bool
    {
        return true; // Авторизация вне объёма задания.
    }

    public function rules(): array
    {
        return [
            // strict: только число JSON; true, "5" и 1.5 не проходят.
            'slot_id' => ['required', 'integer:strict', 'exists:slots,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'regex:'.self::EMAIL_FORMAT, 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'slot_id' => 'слот',
            'customer_name' => 'имя',
            'customer_email' => 'email',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Поле «:attribute» обязательно для заполнения.',
            'integer' => 'Поле «:attribute» должно быть целым числом.',
            'string' => 'Поле «:attribute» должно быть строкой.',
            'email' => 'Укажите корректный email.',
            'customer_email.regex' => 'Укажите корректный email.',
            'max' => 'Поле «:attribute» не должно быть длиннее :max символов.',
            'slot_id.exists' => 'Выбранный слот не существует.',
        ];
    }
}
