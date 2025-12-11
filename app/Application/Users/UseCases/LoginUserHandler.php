<?php

declare(strict_types=1);

namespace App\Application\Users\UseCases;

use App\Domain\Users\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class LoginUserHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    
    public function handle(LoginUserCommand $command): \App\Models\User
    {
        $user = $this->userRepository->findByEmail($command->email);

        if (! $user || ! Hash::check($command->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Неверная пара email/пароль.'],
            ]);
        }

        return $user;
    }
}
