<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Users\UseCases;

use App\Application\Users\UseCases\IssueTokenCommand;
use App\Application\Users\UseCases\IssueTokenHandler;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class IssueTokenHandlerTest extends TestCase
{
    use RefreshDatabase;

    public function test_handle_creates_token_with_short_session_when_remember_is_false(): void
    {
        $user = User::factory()->create();

        $handler = new IssueTokenHandler;

        $command = new IssueTokenCommand(
            user: $user,
            remember: false
        );

        $result = $handler->handle($command);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('expires_at', $result);
        $this->assertArrayHasKey('remember', $result);
        $this->assertInstanceOf(User::class, $result['user']);
        $this->assertIsString($result['token']);
        $this->assertFalse($result['remember']);
        $this->assertNotNull($result['expires_at']);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_handle_creates_token_with_long_session_when_remember_is_true(): void
    {
        $user = User::factory()->create();

        $handler = new IssueTokenHandler;

        $command = new IssueTokenCommand(
            user: $user,
            remember: true
        );

        $result = $handler->handle($command);

        $this->assertIsArray($result);
        $this->assertTrue($result['remember']);
        $this->assertNotNull($result['expires_at']);
        
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
        ]);
    }
}
