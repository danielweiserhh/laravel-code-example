<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Users\UseCases;

use App\Application\Users\UseCases\LoginUserCommand;
use App\Application\Users\UseCases\LoginUserHandler;
use App\Domain\Users\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

final class LoginUserHandlerTest extends TestCase
{
    public function test_handle_returns_user_when_credentials_are_correct(): void
    {
        $user = User::factory()->make([
            'id' => 1,
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $userRepository = Mockery::mock(UserRepositoryInterface::class);
        $userRepository->shouldReceive('findByEmail')
            ->with('test@example.com')
            ->once()
            ->andReturn($user);

        $handler = new LoginUserHandler($userRepository);

        $command = new LoginUserCommand(
            email: 'test@example.com',
            password: 'password123',
            remember: false
        );

        $result = $handler->handle($command);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('test@example.com', $result->email);
    }

    public function test_handle_throws_exception_when_user_not_found(): void
    {
        $userRepository = Mockery::mock(UserRepositoryInterface::class);
        $userRepository->shouldReceive('findByEmail')
            ->with('notfound@example.com')
            ->once()
            ->andReturn(null);

        $handler = new LoginUserHandler($userRepository);

        $command = new LoginUserCommand(
            email: 'notfound@example.com',
            password: 'password123',
            remember: false
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Неверная пара email/пароль.');

        $handler->handle($command);
    }

    public function test_handle_throws_exception_when_password_is_incorrect(): void
    {
        $user = User::factory()->make([
            'id' => 1,
            'email' => 'test@example.com',
            'password' => Hash::make('correct_password'),
        ]);

        $userRepository = Mockery::mock(UserRepositoryInterface::class);
        $userRepository->shouldReceive('findByEmail')
            ->with('test@example.com')
            ->once()
            ->andReturn($user);

        $handler = new LoginUserHandler($userRepository);

        $command = new LoginUserCommand(
            email: 'test@example.com',
            password: 'wrong_password',
            remember: false
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Неверная пара email/пароль.');

        $handler->handle($command);
    }
}
