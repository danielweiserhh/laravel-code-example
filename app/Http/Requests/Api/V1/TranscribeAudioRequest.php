<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class TranscribeAudioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'audio' => ['required', 'file', 'mimes:wav,mp3,m4a,ogg,webm,flac', 'max:10240'],
            'language' => ['nullable', 'string', 'in:auto,ru,en'],
            'model' => ['nullable', 'string', 'in:tiny,base,small,medium,large'],
        ];
    }
}
