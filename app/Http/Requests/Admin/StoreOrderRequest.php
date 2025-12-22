<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:255'],
            'customer_address' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.dish_id' => ['nullable', 'integer', 'exists:dishes,id'],
            'items.*.dish_name' => ['required', 'string', 'max:255'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.constructor_data' => ['nullable', 'array'],
            'items.*.constructor_data.type' => ['nullable', 'string', 'in:constructor'],
            'items.*.constructor_data.categories' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'Имя клиента обязательно для заполнения.',
            'customer_phone.required' => 'Телефон клиента обязателен для заполнения.',
            'items.required' => 'Необходимо добавить хотя бы один товар в заказ.',
            'items.min' => 'Необходимо добавить хотя бы один товар в заказ.',
        ];
    }
}
