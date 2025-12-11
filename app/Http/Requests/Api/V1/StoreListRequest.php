<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'board_id' => 'required|exists:boards,id',
            'name' => 'required|string|max:255',
        ];
    }
    
    public function messages(): array
    {
        return [
            'board_id.required' => 'Поле доска обязательно для заполнения.',
            'board_id.exists' => 'Выбранная доска не существует.',
            'name.required' => 'Поле название колонки обязательно для заполнения.',
            'name.string' => 'Поле название колонки должно быть строкой.',
            'name.max' => 'Поле название колонки не должно превышать 255 символов.',
        ];
    }
}
