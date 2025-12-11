<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'position' => 'sometimes|integer|min:0',
            'is_archived' => 'sometimes|boolean',
        ];
    }
    
    public function messages(): array
    {
        return [
            'name.string' => 'Поле название колонки должно быть строкой.',
            'name.max' => 'Поле название колонки не должно превышать 255 символов.',
            'position.integer' => 'Поле позиция должно быть целым числом.',
            'position.min' => 'Поле позиция не может быть отрицательным.',
            'is_archived.boolean' => 'Поле архив должно быть логическим значением.',
        ];
    }
}
