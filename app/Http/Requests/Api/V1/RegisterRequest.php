<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'name.required' => 'Поле имя обязательно для заполнения.',
            'name.max' => 'Поле имя не должно превышать 255 символов.',
            'email.required' => 'Поле email обязательно для заполнения.',
            'email.email' => 'Поле email должно быть действительным адресом электронной почты.',
            'email.unique' => 'Пользователь с таким email уже зарегистрирован.',
            'email.max' => 'Поле email не должно превышать 255 символов.',
            'password.required' => 'Поле пароль обязательно для заполнения.',
            'password.min' => 'Пароль должен содержать минимум 8 символов.',
            'password.confirmed' => 'Пароли не совпадают.',
        ];
    }
}
