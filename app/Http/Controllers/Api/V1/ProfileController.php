<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Users\UseCases\UpdatePasswordCommand;
use App\Application\Users\UseCases\UpdatePasswordHandler;
use App\Application\Users\UseCases\UpdateProfileCommand;
use App\Application\Users\UseCases\UpdateProfileHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdatePasswordRequest;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    public function __construct(
        private readonly UpdateProfileHandler $updateProfile,
        private readonly UpdatePasswordHandler $updatePassword
    ) {}

    public function show(\Illuminate\Http\Request $request): JsonResponse
    {
        return $this->success($request->user());
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $command = new UpdateProfileCommand(
            userId: $request->user()->id,
            name: $validated['name'] ?? null,
            phone: $validated['phone'] ?? null,
            telegramUsername: $validated['telegram_username'] ?? null,
            aiModel: $validated['ai_model'] ?? null,
            speechModel: $validated['speech_model'] ?? null
        );

        $user = $this->updateProfile->handle($command);

        return $this->success($user, 'Профиль обновлён успешно');
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $command = new UpdatePasswordCommand(
            userId: $user->id,
            password: $validated['password']
        );

        $currentToken = $user->currentAccessToken();
        $this->updatePassword->handle($command, $currentToken);

        return $this->success(null, 'Пароль обновлён успешно');
    }
}
