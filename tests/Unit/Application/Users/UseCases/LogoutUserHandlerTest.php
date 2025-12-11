<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Users\UseCases;

use App\Application\Users\UseCases\LogoutUserCommand;
use App\Application\Users\UseCases\LogoutUserHandler;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LogoutUserHandlerTest extends TestCase
{
    use RefreshDatabase;

    public function test_handle_deletes_current_token_when_delete_all_tokens_is_false(): void
    {
        $user = User::factory()->create();
        $tokenResult = $user->createToken('test-token');
        $tokenId = $tokenResult->accessToken->id;

        $handler = new LogoutUserHandler;

        $command = new LogoutUserCommand(
            user: $user,
            deleteAllTokens: false
        );

        $handler->handle($command);

        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $tokenId,
        ]);
    }

    public function test_handle_deletes_all_tokens_when_delete_all_tokens_is_true(): void
    {
        $user = User::factory()->create();
        $token1 = $user->createToken('test-token-1');
        $token2 = $user->createToken('test-token-2');

        $handler = new LogoutUserHandler;

        $command = new LogoutUserCommand(
            user: $user,
            deleteAllTokens: true
        );

        $handler->handle($command);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token1->accessToken->id,
        ]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token2->accessToken->id,
        ]);
    }
}
