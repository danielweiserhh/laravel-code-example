<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AISuggestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'context_type' => [
                'required',
                Rule::in(['inbox_item', 'today_plan', 'card_steps', 'card_rewrite', 'card_assist', 'card_format', 'card_decompose', 'card_improve']),
            ],
            'context_id' => [
                Rule::requiredIf(fn () => in_array($this->input('context_type'), ['inbox_item', 'card_steps', 'card_rewrite', 'card_assist'], true)),
                'integer',
            ],
            'options' => ['nullable', 'array'],
        ];
    }
}
