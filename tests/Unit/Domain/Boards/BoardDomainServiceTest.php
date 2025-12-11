<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Boards;

use App\Domain\Boards\Repositories\BoardRepositoryInterface;
use App\Domain\Boards\Services\BoardDomainService;
use App\Enums\BoardPrivacy;
use Tests\TestCase;

class BoardDomainServiceTest extends TestCase
{
    private BoardRepositoryInterface $boardRepository;
    private BoardDomainService $boardDomainService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->boardRepository = $this->mock(BoardRepositoryInterface::class);
        $this->boardDomainService = new BoardDomainService($this->boardRepository);
    }

    public function test_validate_name_throws_when_empty(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Название доски не может быть пустым');

        $this->boardDomainService->validateName('');
    }

    public function test_validate_name_throws_when_too_long(): void
    {
        $longName = str_repeat('a', 256);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Название доски не может быть длиннее 255 символов');

        $this->boardDomainService->validateName($longName);
    }

    public function test_validate_name_allows_valid_name(): void
    {
        $this->boardDomainService->validateName('Valid Board Name');
    }

    public function test_get_default_privacy_returns_private(): void
    {
        $privacy = $this->boardDomainService->getDefaultPrivacy();

        $this->assertSame(BoardPrivacy::PRIVATE, $privacy);
    }

    public function test_can_user_access_returns_true_for_public_board(): void
    {
        $boardId = 1;
        $userId = 1;
        $privacy = BoardPrivacy::PUBLIC;

        $result = $this->boardDomainService->canUserAccess($boardId, $userId, $privacy);

        $this->assertTrue($result);
    }

    public function test_can_user_access_returns_true_when_user_is_member(): void
    {
        $boardId = 1;
        $userId = 1;
        $privacy = BoardPrivacy::PRIVATE;

        $this->boardRepository
            ->shouldReceive('isUserMember')
            ->once()
            ->with(1, 1)
            ->andReturn(true);

        $result = $this->boardDomainService->canUserAccess($boardId, $userId, $privacy);

        $this->assertTrue($result);
    }

    public function test_can_user_access_returns_false_when_user_is_not_member(): void
    {
        $boardId = 1;
        $userId = 1;
        $privacy = BoardPrivacy::PRIVATE;

        $this->boardRepository
            ->shouldReceive('isUserMember')
            ->once()
            ->with(1, 1)
            ->andReturn(false);

        $result = $this->boardDomainService->canUserAccess($boardId, $userId, $privacy);

        $this->assertFalse($result);
    }

    public function test_can_user_modify_returns_true_when_user_is_admin(): void
    {
        $boardId = 1;
        $userId = 1;

        $this->boardRepository
            ->shouldReceive('isUserAdminOrOwner')
            ->once()
            ->with(1, 1)
            ->andReturn(true);

        $result = $this->boardDomainService->canUserModify($boardId, $userId);

        $this->assertTrue($result);
    }

    public function test_can_user_modify_returns_false_when_user_is_not_admin(): void
    {
        $boardId = 1;
        $userId = 1;

        $this->boardRepository
            ->shouldReceive('isUserAdminOrOwner')
            ->once()
            ->with(1, 1)
            ->andReturn(false);

        $result = $this->boardDomainService->canUserModify($boardId, $userId);

        $this->assertFalse($result);
    }
}
