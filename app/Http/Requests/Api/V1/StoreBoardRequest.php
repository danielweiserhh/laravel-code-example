<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Enums\BoardPrivacy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBoardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'workspace_id' => 'required|exists:workspaces,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7',
            'privacy' => ['nullable', Rule::enum(BoardPrivacy::class)],
            'settings' => 'nullable|array',
        ];
    }
    
    public function messages(): array
    {
        return [
            'workspace_id.required' => 'Поле рабочее пространство обязательно для заполнения.',
            'workspace_id.exists' => 'Выбранное рабочее пространство не существует.',
            'name.required' => 'Поле название доски обязательно для заполнения.',
            'name.string' => 'Поле название доски должно быть строкой.',
            'name.max' => 'Поле название доски не должно превышать 255 символов.',
            'description.string' => 'Поле описание должно быть строкой.',
            'description.max' => 'Поле описание не должно превышать 1000 символов.',
            'color.string' => 'Поле цвет должно быть строкой.',
            'color.max' => 'Поле цвет не должно превышать 7 символов.',
            'privacy.enum' => 'Выбранное значение приватности недействительно.',
            'settings.array' => 'Поле настройки должно быть массивом.',
        ];
    }
}
