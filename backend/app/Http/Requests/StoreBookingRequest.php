<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Авторизация вне объёма задания.
    }

    public function rules(): array
    {
        return [
            'slot_id' => ['required', 'integer', 'exists:slots,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
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
            'max' => 'Поле «:attribute» не должно быть длиннее :max символов.',
            'slot_id.exists' => 'Выбранный слот не существует.',
        ];
    }
}
