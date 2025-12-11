<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChecklistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
        ];
    }
    
    public function messages(): array
    {
        return [
            'title.string' => 'Поле название чеклиста должно быть строкой.',
            'title.max' => 'Поле название чеклиста не должно превышать 255 символов.',
        ];
    }
}
