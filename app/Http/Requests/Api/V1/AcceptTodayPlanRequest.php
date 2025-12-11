<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class AcceptTodayPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'job_id' => ['nullable', 'uuid'],
            'big_three' => ['array'],
            'big_three.*.id' => ['required', 'integer'],
            'big_three.*.title' => ['nullable', 'string'],
            'big_three.*.board' => ['nullable', 'string'],
            'big_three.*.due_date' => ['nullable', 'string'],
            'big_three.*.energy_level' => ['nullable', 'string'],
            'note_for_user' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'big_three' => $this->input('big_three', []),
        ]);
    }
}
