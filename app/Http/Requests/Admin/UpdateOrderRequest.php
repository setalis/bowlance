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
            'customer_name' => ['sometimes', 'required', 'string', 'max:255'],
            'customer_phone' => ['sometimes', 'required', 'string', 'max:255'],
            'customer_address' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:new,preparing,delivering,completed'],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.dish_id' => ['required_with:items', 'integer', 'exists:dishes,id'],
            'items.*.dish_name' => ['required_with:items', 'string', 'max:255'],
            'items.*.price' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'Имя клиента обязательно для заполнения.',
            'customer_phone.required' => 'Телефон клиента обязателен для заполнения.',
            'status.required' => 'Статус заказа обязателен для заполнения.',
            'status.in' => 'Недопустимый статус заказа.',
            'items.min' => 'Необходимо добавить хотя бы один товар в заказ.',
        ];
    }
}
