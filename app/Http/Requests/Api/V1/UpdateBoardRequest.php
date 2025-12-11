<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Enums\BoardPrivacy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBoardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7',
            'privacy' => ['nullable', Rule::enum(BoardPrivacy::class)],
            'is_favorite' => 'sometimes|boolean',
            'settings' => 'nullable|array',
        ];
    }
    
    public function messages(): array
    {
        return [
            'name.string' => 'Поле название доски должно быть строкой.',
            'name.max' => 'Поле название доски не должно превышать 255 символов.',
            'description.string' => 'Поле описание должно быть строкой.',
            'description.max' => 'Поле описание не должно превышать 1000 символов.',
            'color.string' => 'Поле цвет должно быть строкой.',
            'color.max' => 'Поле цвет не должно превышать 7 символов.',
            'privacy.enum' => 'Выбранное значение приватности недействительно.',
            'is_favorite.boolean' => 'Поле избранное должно быть логическим значением.',
            'settings.array' => 'Поле настройки должно быть массивом.',
        ];
    }
}
