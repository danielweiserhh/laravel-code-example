<?php

declare(strict_types=1);

namespace App\Application\Users\UseCases;

use Illuminate\Support\Carbon;

final class IssueTokenHandler
{
    
    public function handle(IssueTokenCommand $command): array
    {
        $abilities = ['*', $command->remember ? 'session:long' : 'session:short'];
        $expiresAt = $command->remember
            ? Carbon::now()->addDays((int) config('auth.tokens.long_lifetime_days', 60))
            : Carbon::now()->addMinutes((int) config('auth.tokens.short_lifetime_minutes', 60));

        $tokenResult = $command->user->createToken(
            'auth-token',
            $abilities,
            $expiresAt
        );

        $command->user->refresh();

        return [
            'user' => $command->user,
            'token' => $tokenResult->plainTextToken,
            'expires_at' => optional($tokenResult->accessToken->expires_at)->toIso8601String(),
            'remember' => $command->remember,
        ];
    }
}
