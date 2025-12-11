<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CreateInboxItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:5000'],
            'workspace_id' => ['nullable', 'integer', 'exists:workspaces,id'],
            'source' => ['nullable', 'string', 'in:manual,email,api,voice'],
            'ai_parse' => ['nullable', 'boolean'],
        ];
    }
}
