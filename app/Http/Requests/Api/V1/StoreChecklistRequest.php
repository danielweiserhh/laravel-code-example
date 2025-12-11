<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreChecklistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'card_id' => 'required|exists:cards,id',
            'title' => 'required|string|max:255',
            'items' => 'nullable|array',
            'items.*.title' => 'required|string|max:255',
            'items.*.position' => 'nullable|integer',
            'items.*.is_completed' => 'nullable|boolean',
        ];
    }
    
    public function messages(): array
    {
        return [
            'card_id.required' => 'Поле задача обязательно для заполнения.',
            'card_id.exists' => 'Выбранная задача не существует.',
            'title.required' => 'Поле название чеклиста обязательно для заполнения.',
            'title.string' => 'Поле название чеклиста должно быть строкой.',
            'title.max' => 'Поле название чеклиста не должно превышать 255 символов.',
            'items.array' => 'Поле элементы должно быть массивом.',
            'items.*.title.required' => 'Поле название элемента обязательно для заполнения.',
            'items.*.title.string' => 'Поле название элемента должно быть строкой.',
            'items.*.title.max' => 'Поле название элемента не должно превышать 255 символов.',
            'items.*.position.integer' => 'Поле позиция элемента должно быть целым числом.',
            'items.*.is_completed.boolean' => 'Поле выполнено должно быть логическим значением.',
        ];
    }
}
