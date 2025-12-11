<?php

declare(strict_types=1);

namespace Tests\Feature\Middleware;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class RefreshTokenExpiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_sets_token_expiry_headers_for_short_session(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token', ['session:short'])->plainTextToken;

        $response = $this->getJson('/api/v1/workspaces', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertHeader('X-Token-Expires-At');
        $response->assertHeader('X-Token-Remember', '0');

        $expiresAt = Carbon::parse($response->headers->get('X-Token-Expires-At'));
        $this->assertTrue($expiresAt->isFuture());
        $this->assertLessThanOrEqual(61, $expiresAt->diffInMinutes(Carbon::now())); 
    }

    public function test_sets_remember_header_for_long_session(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token', ['session:long'])->plainTextToken;

        $response = $this->getJson('/api/v1/workspaces', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertHeader('X-Token-Remember', '1');
    }

    public function test_updates_token_expiry_on_each_request_for_short_session(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token', ['session:short'])->plainTextToken;

        
        $response1 = $this->getJson('/api/v1/workspaces', [
            'Authorization' => "Bearer {$token}",
        ]);
        $expiresAt1 = Carbon::parse($response1->headers->get('X-Token-Expires-At'));

        
        Carbon::setTestNow(Carbon::now()->addMinutes(5));

        
        $response2 = $this->getJson('/api/v1/workspaces', [
            'Authorization' => "Bearer {$token}",
        ]);
        $expiresAt2 = Carbon::parse($response2->headers->get('X-Token-Expires-At'));

        
        $this->assertTrue($expiresAt2->isAfter($expiresAt1));
    }

    public function test_does_not_set_headers_for_unauthenticated_request(): void
    {
        $response = $this->getJson('/api/v1/workspaces');

        
        $this->assertFalse($response->headers->has('X-Token-Expires-At'));
        $this->assertFalse($response->headers->has('X-Token-Remember'));
    }

    public function test_handles_token_without_expiry(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token', ['*'])->plainTextToken;

        
        $tokenModel = PersonalAccessToken::findToken($token);
        $tokenModel->expires_at = null;
        $tokenModel->save();

        $response = $this->getJson('/api/v1/workspaces', [
            'Authorization' => "Bearer {$token}",
        ]);

        
        $this->assertFalse($response->headers->has('X-Token-Expires-At'));
        $response->assertHeader('X-Token-Remember', '0');
    }

    public function test_handles_token_near_expiry(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token', ['session:short'])->plainTextToken;

        
        $tokenModel = PersonalAccessToken::findToken($token);
        $tokenModel->expires_at = Carbon::now()->addMinute();
        $tokenModel->save();

        $response = $this->getJson('/api/v1/workspaces', [
            'Authorization' => "Bearer {$token}",
        ]);

        $expiresAt = Carbon::parse($response->headers->get('X-Token-Expires-At'));
        
        $now = Carbon::now();
        $minutesUntilExpiry = $expiresAt->diffInMinutes($now, false); 
        
        $minutesUntilExpiry = abs($minutesUntilExpiry);
        $this->assertGreaterThanOrEqual(59, $minutesUntilExpiry);
        $this->assertLessThanOrEqual(61, $minutesUntilExpiry);
    }
}
