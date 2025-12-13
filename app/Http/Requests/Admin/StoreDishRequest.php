<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreDishRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'dish_category_id' => ['required', 'exists:dish_categories,id'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'weight_volume' => ['nullable', 'string', 'max:50'],
            'calories' => ['nullable', 'integer', 'min:0'],
            'proteins' => ['nullable', 'numeric', 'min:0'],
            'fats' => ['nullable', 'numeric', 'min:0'],
            'carbohydrates' => ['nullable', 'numeric', 'min:0'],
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
            'name.required' => 'Название блюда обязательно для заполнения.',
            'name.max' => 'Название блюда не должно превышать 255 символов.',
            'price.required' => 'Цена обязательна для заполнения.',
            'price.numeric' => 'Цена должна быть числом.',
            'price.min' => 'Цена не может быть отрицательной.',
            'dish_category_id.required' => 'Категория обязательна для выбора.',
            'dish_category_id.exists' => 'Выбранная категория не существует.',
            'image.image' => 'Файл должен быть изображением.',
            'image.mimes' => 'Изображение должно быть в формате: jpeg, png, jpg, gif или webp.',
            'image.max' => 'Размер изображения не должен превышать 2 МБ.',
            'weight_volume.max' => 'Вес/объем не должен превышать 50 символов.',
            'calories.integer' => 'Калории должны быть целым числом.',
            'calories.min' => 'Калории не могут быть отрицательными.',
            'proteins.numeric' => 'Белки должны быть числом.',
            'proteins.min' => 'Белки не могут быть отрицательными.',
            'fats.numeric' => 'Жиры должны быть числом.',
            'fats.min' => 'Жиры не могут быть отрицательными.',
            'carbohydrates.numeric' => 'Углеводы должны быть числом.',
            'carbohydrates.min' => 'Углеводы не могут быть отрицательными.',
            'sort_order.integer' => 'Порядок должен быть целым числом.',
            'sort_order.min' => 'Порядок не может быть отрицательным.',
        ];
    }
}
