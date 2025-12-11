<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Users\UseCases;

use App\Application\Users\UseCases\RegisterUserCommand;
use App\Application\Users\UseCases\RegisterUserHandler;
use App\Domain\Users\Repositories\UserRepositoryInterface;
use App\Models\User;
use App\Services\DefaultDataService;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

final class RegisterUserHandlerTest extends TestCase
{
    public function test_handle_creates_user_and_default_workspace(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ];

        $user = User::factory()->make($userData);
        $user->id = 1;

        $userRepository = Mockery::mock(UserRepositoryInterface::class);
        $userRepository->shouldReceive('create')
            ->with(Mockery::on(function ($data) use ($userData) {
                return $data['name'] === $userData['name']
                    && $data['email'] === $userData['email']
                    && Hash::check('password123', $data['password']);
            }))
            ->once()
            ->andReturn($user);

        $defaultDataService = Mockery::mock(DefaultDataService::class);
        $defaultDataService->shouldReceive('createDefaultWorkspaceForUser')
            ->with($user)
            ->once();

        $handler = new RegisterUserHandler($userRepository, $defaultDataService);

        $command = new RegisterUserCommand(
            name: 'Test User',
            email: 'test@example.com',
            password: 'password123',
            remember: false
        );

        $result = $handler->handle($command);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('Test User', $result->name);
        $this->assertEquals('test@example.com', $result->email);
    }
}
