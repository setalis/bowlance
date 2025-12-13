<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDishCategoryRequest extends FormRequest
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
        $categoryId = $this->route('dish_category')->id;

        return [
            'name' => ['required', 'string', 'max:255', 'unique:dish_categories,name,'.$categoryId],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Название категории обязательно для заполнения.',
            'name.unique' => 'Категория с таким названием уже существует.',
            'name.max' => 'Название категории не должно превышать 255 символов.',
            'image.image' => 'Файл должен быть изображением.',
            'image.mimes' => 'Изображение должно быть в формате: jpeg, png, jpg, gif или webp.',
            'image.max' => 'Размер изображения не должен превышать 2 МБ.',
            'sort_order.integer' => 'Порядок сортировки должен быть целым числом.',
            'sort_order.min' => 'Порядок сортировки не может быть отрицательным.',
        ];
    }
}
