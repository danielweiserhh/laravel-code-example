<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class RefreshTokenExpiry
{
    private const SHORT_SESSION_MINUTES = 60;
    private const REFRESH_THRESHOLD_MINUTES = 5; 
    
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $user = $request->user();

        if (! $user) {
            return $response;
        }

        $token = $user->currentAccessToken();

        if (! $token instanceof PersonalAccessToken) {
            return $response;
        }

        $abilities = $token->abilities ?? [];
        $now = Carbon::now();
        
        if (\in_array('session:short', $abilities, true)) {
            $token->forceFill([
                'expires_at' => $now->copy()->addMinutes(self::SHORT_SESSION_MINUTES),
            ])->save();
        } elseif ($token->expires_at) {
            $minutesRemaining = $now->diffInMinutes($token->expires_at, false);

            if ($minutesRemaining > 0 && $minutesRemaining < self::REFRESH_THRESHOLD_MINUTES) {
                
                $token->forceFill([
                    'expires_at' => $now->copy()->addDays(30), 
                ])->save();
            }
        }

        if ($token->expires_at) {
            $response->headers->set('X-Token-Expires-At', $token->expires_at->toIso8601String());
        }

        $response->headers->set(
            'X-Token-Remember',
            \in_array('session:long', $abilities, true) ? '1' : '0'
        );

        return $response;
    }
}
