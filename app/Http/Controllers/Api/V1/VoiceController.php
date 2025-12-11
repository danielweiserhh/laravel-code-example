<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Speech\UseCases\TranscribeAudioCommand;
use App\Application\Speech\UseCases\TranscribeAudioHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\TranscribeAudioRequest;
use Illuminate\Http\JsonResponse;

class VoiceController extends Controller
{
    public function __construct(
        private readonly TranscribeAudioHandler $transcribeHandler
    ) {}

    public function transcribe(TranscribeAudioRequest $request): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return $this->error('Требуется авторизация', 401);
        }

        $validated = $request->validated();
        $audioFile = $request->file('audio');

        if ($audioFile === null) {
            return $this->error('Аудиофайл не найден', 400);
        }

        $filePath = $audioFile->getRealPath();

        if ($filePath === false) {
            return $this->error('Не удалось прочитать аудиофайл', 400);
        }

        $audioContent = file_get_contents($filePath);
        
        if ($audioContent === false) {
            return $this->error('Не удалось прочитать содержимое аудиофайла', 400);
        }

        $result = $this->transcribeHandler->handle(
            new TranscribeAudioCommand(
                userId: $user->id,
                audioContent: $audioContent,
                fileName: $audioFile->getClientOriginalName(),
                language: $validated['language'] ?? 'auto',
                model: $validated['model'] ?? ($user->speech_model ?? 'medium')
            )
        );

        return $this->success($result);
    }
}
