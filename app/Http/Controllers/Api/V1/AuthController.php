<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Users\UseCases\IssueTokenCommand;
use App\Application\Users\UseCases\IssueTokenHandler;
use App\Application\Users\UseCases\LoginUserCommand;
use App\Application\Users\UseCases\LoginUserHandler;
use App\Application\Users\UseCases\LogoutUserCommand;
use App\Application\Users\UseCases\LogoutUserHandler;
use App\Application\Users\UseCases\RegisterUserCommand;
use App\Application\Users\UseCases\RegisterUserHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly RegisterUserHandler $registerUser,
        private readonly LoginUserHandler $loginUser,
        private readonly IssueTokenHandler $issueToken,
        private readonly LogoutUserHandler $logoutUser
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = $this->registerUser->handle(
            new RegisterUserCommand(
                name: $validated['name'],
                email: $validated['email'],
                password: $validated['password'],
                remember: $validated['remember'] ?? false,
            )
        );

        $result = $this->issueToken->handle(
            new IssueTokenCommand(
                user: $user,
                remember: $validated['remember'] ?? false,
            )
        );

        $response = $this->created($result, 'Регистрация выполнена успешно');

        return $this->withTokenHeaders($response, $result);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = $this->loginUser->handle(
            new LoginUserCommand(
                email: $validated['email'],
                password: $validated['password'],
                remember: $validated['remember'] ?? false,
            )
        );

        $result = $this->issueToken->handle(
            new IssueTokenCommand(
                user: $user,
                remember: $validated['remember'] ?? false,
            )
        );

        $response = $this->success($result, 'Вход выполнен успешно');

        return $this->withTokenHeaders($response, $result);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->logoutUser->handle(
            new LogoutUserCommand(
                user: $request->user(),
                deleteAllTokens: false,
            )
        );

        return $this->success(null, 'Выход выполнен успешно');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success($request->user());
    }

    private function withTokenHeaders(JsonResponse $response, array $payload): JsonResponse
    {
        if (! empty($payload['expires_at'])) {
            $response->headers->set('X-Token-Expires-At', (string) $payload['expires_at']);
        }

        if (array_key_exists('remember', $payload)) {
            $response->headers->set('X-Token-Remember', $payload['remember'] ? '1' : '0');
        }

        return $response;
    }
}
