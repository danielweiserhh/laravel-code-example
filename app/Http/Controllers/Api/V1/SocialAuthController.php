<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Users\UseCases\IssueTokenCommand;
use App\Application\Users\UseCases\IssueTokenHandler;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DefaultDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function __construct(
        private readonly IssueTokenHandler $issueToken,
        private readonly DefaultDataService $defaultDataService
    ) {}
    
    public function redirectToGoogle(): RedirectResponse
    {
        $driver = Socialite::driver('google');

        return $driver->stateless()->redirect();
    }
    
    public function handleGoogleCallback(): JsonResponse|RedirectResponse
    {
        try {
            
            $driver = Socialite::driver('google');
            $googleUser = $driver->stateless()->user();
        } catch (\Exception $e) {
            return $this->redirectToFrontendWithError('Не удалось получить данные от Google');
        }

        $user = $this->findOrCreateUser($googleUser);

        $result = $this->issueToken->handle(
            new IssueTokenCommand(
                user: $user,
                remember: true,
            )
        );
        
        return $this->redirectToFrontendWithToken($result);
    }
    
    private function findOrCreateUser($googleUser): User
    { 
        $user = User::where('google_id', $googleUser->getId())->first();

        if ($user) {
            if ($googleUser->getAvatar() && $user->avatar !== $googleUser->getAvatar()) {
                $user->update(['avatar' => $googleUser->getAvatar()]);
            }

            return $user;
        }
        
        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar() ?? $user->avatar,
            ]);

            return $user;
        }

        return DB::transaction(function () use ($googleUser) {
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'password' => null, 
                'email_verified_at' => now(), 
            ]);
            
            $this->defaultDataService->createDefaultWorkspaceForUser($user);

            return $user;
        });
    }
    
    private function redirectToFrontendWithToken(array $tokenData): RedirectResponse
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
        $token = $tokenData['token'] ?? '';
        $expiresAt = $tokenData['expires_at'] ?? '';

        $params = http_build_query([
            'token' => $token,
            'expires_at' => $expiresAt,
            'provider' => 'google',
        ]);

        return redirect("{$frontendUrl}/auth/callback?{$params}");
    }
    
    private function redirectToFrontendWithError(string $message): RedirectResponse
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:3000');

        $params = http_build_query([
            'error' => $message,
            'provider' => 'google',
        ]);

        return redirect("{$frontendUrl}/auth/callback?{$params}");
    }
}
