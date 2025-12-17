<?php

namespace App\Http\Requests\PhoneVerification;

use Illuminate\Foundation\Http\FormRequest;

class VerifyPhoneCodeRequest extends FormRequest
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
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'code' => ['required', 'string', 'size:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => 'ID заказа обязателен.',
            'order_id.exists' => 'Заказ не найден.',
            'code.required' => 'Код подтверждения обязателен.',
            'code.size' => 'Код должен состоять из 6 цифр.',
        ];
    }
}
