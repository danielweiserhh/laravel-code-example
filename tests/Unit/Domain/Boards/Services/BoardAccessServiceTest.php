<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Boards\Services;

use App\Domain\Boards\Enums\BoardPrivacy;
use App\Domain\Boards\Repositories\BoardRepositoryInterface;
use App\Domain\Boards\Services\BoardAccessService;
use App\Domain\Boards\ValueObjects\Board;
use Mockery\MockInterface;
use Tests\TestCase;

final class BoardAccessServiceTest extends TestCase
{
    private BoardRepositoryInterface $boardRepository;
    private BoardAccessService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->boardRepository = $this->mock(BoardRepositoryInterface::class);
        $this->service = new BoardAccessService($this->boardRepository);
    }

    public function test_can_user_view_returns_true_for_public_board(): void
    {
        $board = Board::fromArray([
            'id' => 1,
            'workspace_id' => 1,
            'name' => 'Public Board',
            'privacy' => BoardPrivacy::PUBLIC->value,
            'is_favorite' => false,
            'position' => 0,
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ]);

        $this->boardRepository
            ->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($board);

        $result = $this->service->canUserView(1, 999);

        $this->assertTrue($result);
    }

    public function test_can_user_view_returns_true_when_user_is_member(): void
    {
        $board = Board::fromArray([
            'id' => 1,
            'workspace_id' => 1,
            'name' => 'Private Board',
            'privacy' => BoardPrivacy::PRIVATE->value,
            'is_favorite' => false,
            'position' => 0,
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ]);

        $this->boardRepository
            ->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($board);

        $this->boardRepository
            ->shouldReceive('isUserMember')
            ->once()
            ->with(1, 100)
            ->andReturn(true);

        $result = $this->service->canUserView(1, 100);

        $this->assertTrue($result);
    }

    public function test_can_user_view_returns_false_when_user_is_not_member(): void
    {
        $board = Board::fromArray([
            'id' => 1,
            'workspace_id' => 1,
            'name' => 'Private Board',
            'privacy' => BoardPrivacy::PRIVATE->value,
            'is_favorite' => false,
            'position' => 0,
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ]);

        $this->boardRepository
            ->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($board);

        $this->boardRepository
            ->shouldReceive('isUserMember')
            ->once()
            ->with(1, 100)
            ->andReturn(false);

        $result = $this->service->canUserView(1, 100);

        $this->assertFalse($result);
    }

    public function test_can_user_view_throws_when_board_not_found(): void
    {
        $this->boardRepository
            ->shouldReceive('find')
            ->once()
            ->with(999)
            ->andReturn(null);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Доска не найдена');

        $this->service->canUserView(999, 1);
    }

    public function test_can_user_modify_returns_true_when_user_is_admin(): void
    {
        $board = Board::fromArray([
            'id' => 1,
            'workspace_id' => 1,
            'name' => 'Board',
            'privacy' => BoardPrivacy::PRIVATE->value,
            'is_favorite' => false,
            'position' => 0,
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ]);

        $this->boardRepository
            ->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($board);

        $this->boardRepository
            ->shouldReceive('isUserAdminOrOwner')
            ->once()
            ->with(1, 100)
            ->andReturn(true);

        $result = $this->service->canUserModify(1, 100);

        $this->assertTrue($result);
    }

    public function test_can_user_delete_returns_true_when_user_is_owner(): void
    {
        $board = Board::fromArray([
            'id' => 1,
            'workspace_id' => 1,
            'name' => 'Board',
            'privacy' => BoardPrivacy::PRIVATE->value,
            'is_favorite' => false,
            'position' => 0,
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ]);

        $this->boardRepository
            ->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($board);

        $this->boardRepository
            ->shouldReceive('isUserAdminOrOwner')
            ->once()
            ->with(1, 100)
            ->andReturn(true);

        $result = $this->service->canUserDelete(1, 100);

        $this->assertTrue($result);
    }
}
