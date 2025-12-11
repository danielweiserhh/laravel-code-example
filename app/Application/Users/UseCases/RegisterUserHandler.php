<?php

declare(strict_types=1);

namespace App\Application\Users\UseCases;

use App\Domain\Users\Repositories\UserRepositoryInterface;
use App\Models\User;
use App\Services\DefaultDataService;
use Illuminate\Support\Facades\Hash;

final class RegisterUserHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly DefaultDataService $defaultDataService
    ) {}

    
    public function handle(RegisterUserCommand $command): User
    {
        $user = $this->userRepository->create([
            'name' => $command->name,
            'email' => $command->email,
            'password' => Hash::make($command->password),
        ]);

        
        $this->defaultDataService->createDefaultWorkspaceForUser($user);

        return $user;
    }
}
