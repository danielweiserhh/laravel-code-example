<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class MoveCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'list_id' => 'required|exists:lists,id',
            'position' => 'nullable|integer|min:0',
        ];
    }
    
    public function messages(): array
    {
        return [
            'list_id.required' => 'Поле колонка обязательно для заполнения.',
            'list_id.exists' => 'Выбранная колонка не существует.',
            'position.integer' => 'Поле позиция должно быть целым числом.',
            'position.min' => 'Поле позиция не может быть отрицательным.',
        ];
    }
}
