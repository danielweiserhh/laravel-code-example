<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class AssistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'assist_type' => 'required|string|in:start_task,stuck,overwhelmed',
            'card_id' => 'nullable|exists:cards,id',
            'context' => 'nullable|string|max:1000',
            'workspace_id' => 'nullable|exists:workspaces,id',
            'language' => 'nullable|string|in:ru,en',
        ];
    }
}
