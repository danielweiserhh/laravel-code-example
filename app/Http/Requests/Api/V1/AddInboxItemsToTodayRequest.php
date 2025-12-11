<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class AddInboxItemsToTodayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'inbox_item_ids' => ['required', 'array', 'min:1'],
            'inbox_item_ids.*' => ['required', 'integer', 'exists:inbox_items,id'],
        ];
    }
}
