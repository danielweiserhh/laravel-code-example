<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'telegram_username' => ['nullable', 'string', 'regex:/^@?[a-zA-Z0-9_]{3,64}$/'],
            'ai_model' => ['nullable', 'string', 'in:qwen2.5:3b,qwen2.5:7b,qwen2.5:14b'],
            'speech_model' => ['nullable', 'string', 'in:tiny,base,small,medium,large'],
        ];
    }
    
    public function attributes(): array
    {
        return [
            'name' => 'имя',
            'phone' => 'номер телефона',
            'telegram_username' => 'telegram ник',
            'ai_model' => 'модель ИИ',
            'speech_model' => 'модель речи',
        ];
    }
    
    public function messages(): array
    {
        return [
            'name.required' => 'Поле имя обязательно для заполнения.',
            'name.max' => 'Поле имя не должно превышать 255 символов.',
            'phone.max' => 'Поле номер телефона не должно превышать 32 символов.',
            'telegram_username.regex' => 'Telegram ник должен начинаться с @ и содержать от 3 до 64 символов (буквы, цифры, подчёркивания).',
            'ai_model.in' => 'Выбрана недопустимая модель ИИ.',
            'speech_model.in' => 'Выбрана недопустимая модель речи.',
        ];
    }
}
