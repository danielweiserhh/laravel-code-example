<?php

declare(strict_types=1);

namespace App\Application\Users\UseCases;

use Laravel\Sanctum\PersonalAccessToken;

final class LogoutUserHandler
{
    
    public function handle(LogoutUserCommand $command): void
    {
        if ($command->deleteAllTokens) {
            $command->user->tokens()->delete();

            return;
        }

        
        
        $currentToken = $command->user->currentAccessToken();
        if ($currentToken instanceof PersonalAccessToken) {
            $currentToken->delete();
        }
    }
}
