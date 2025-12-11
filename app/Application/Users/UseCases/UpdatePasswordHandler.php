<?php

declare(strict_types=1);

namespace App\Application\Users\UseCases;

use App\Domain\Users\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

final class UpdatePasswordHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    
    public function handle(UpdatePasswordCommand $command, ?PersonalAccessToken $currentToken = null): void
    {
        DB::transaction(function () use ($command, $currentToken) {
            $user = $this->userRepository->findOrFail($command->userId);

            $user->forceFill([
                'password' => Hash::make($command->password),
            ]);
            $this->userRepository->save($user);

            
            
            if ($currentToken === null) {
                $currentToken = $user->currentAccessToken();
            }

            if ($currentToken instanceof PersonalAccessToken && $currentToken->id) {
                
                $user->tokens()->where('id', '!=', $currentToken->id)->delete();
            } else {
                
                $user->tokens()->delete();
            }
        });
    }
}
