<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
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
            'status' => ['required', 'string', 'in:new,preparing,delivering,completed'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Статус заказа обязателен для заполнения.',
            'status.in' => 'Недопустимый статус заказа.',
        ];
    }
}
