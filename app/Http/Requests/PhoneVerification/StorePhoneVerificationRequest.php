<?php

namespace App\Http\Requests\PhoneVerification;

use Illuminate\Foundation\Http\FormRequest;

class StorePhoneVerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:255'],
            'telegram_chat_id' => ['required', 'string', 'max:255'],
            'order_id' => ['required', 'integer', 'exists:orders,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Телефон обязателен для заполнения.',
            'telegram_chat_id.required' => 'Telegram Chat ID обязателен для заполнения.',
            'order_id.required' => 'ID заказа обязателен.',
            'order_id.exists' => 'Заказ не найден.',
        ];
    }
}
