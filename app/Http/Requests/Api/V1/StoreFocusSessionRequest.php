<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreFocusSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'card_id' => 'nullable|exists:cards,id',
            'duration_minutes' => 'nullable|integer|min:1|max:120',
            'is_group' => 'nullable|boolean',
            'video_link' => 'nullable|url|max:500',
        ];
    }
}
