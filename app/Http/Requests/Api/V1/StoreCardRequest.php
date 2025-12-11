<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Enums\EnergyLevel;
use App\Enums\TaskType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'list_id' => 'required|exists:lists,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'duration' => [
                'nullable',
                'string',
                'regex:/^(\d+h)?(\d+m)?$/i',
            ],
            'energy_level' => ['nullable', Rule::enum(EnergyLevel::class)],
            'task_type' => ['nullable', Rule::enum(TaskType::class)],
            'assignee_id' => 'nullable|exists:users,id',
            
            'checklist' => 'nullable|array',
        ];
    }
    
    public function messages(): array
    {
        return [
            'list_id.required' => 'Поле колонка обязательно для заполнения.',
            'list_id.exists' => 'Выбранная колонка не существует.',
            'title.required' => 'Поле название задачи обязательно для заполнения.',
            'title.string' => 'Поле название задачи должно быть строкой.',
            'title.max' => 'Поле название задачи не должно превышать 255 символов.',
            'description.string' => 'Поле описание должно быть строкой.',
            'start_date.date' => 'Поле дата начала должно быть датой.',
            'due_date.date' => 'Поле дата окончания должно быть датой.',
            'due_date.after_or_equal' => 'Поле дата окончания должно быть не раньше даты начала.',
            'duration.string' => 'Поле длительность должно быть строкой.',
            'duration.regex' => 'Поле длительность имеет неверный формат (например, 1h30m).',
            'energy_level.enum' => 'Выбранный уровень энергии недействителен.',
            'task_type.enum' => 'Выбранный тип задачи недействителен.',
            'assignee_id.exists' => 'Выбранный исполнитель не существует.',
            'checklist.array' => 'Поле чеклист должно быть массивом.',
        ];
    }
}
