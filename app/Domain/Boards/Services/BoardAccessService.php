<?php

declare(strict_types=1);

namespace App\Domain\Boards\Services;

use App\Domain\Boards\Enums\BoardPrivacy;
use App\Domain\Boards\Repositories\BoardRepositoryInterface;

final class BoardAccessService
{
    public function __construct(
        private readonly BoardRepositoryInterface $boardRepository
    ) {}

    public function canUserView(int $boardId, int $userId): bool
    {
        $board = $this->boardRepository->find($boardId);
        if ($board === null) {
            throw new \DomainException('Доска не найдена');
        }

        if ($board->privacy === BoardPrivacy::PUBLIC) {
            return true;
        }

        return $this->boardRepository->isUserMember($boardId, $userId);
    }

    public function canUserModify(int $boardId, int $userId): bool
    {
        $board = $this->boardRepository->find($boardId);
        if ($board === null) {
            throw new \DomainException('Доска не найдена');
        }

        return $this->boardRepository->isUserAdminOrOwner($boardId, $userId);
    }

    public function canUserDelete(int $boardId, int $userId): bool
    {
        $board = $this->boardRepository->find($boardId);
        if ($board === null) {
            throw new \DomainException('Доска не найдена');
        }

        return $this->boardRepository->isUserAdminOrOwner($boardId, $userId);
    }

    public function isUserMember(int $boardId, int $userId): bool
    {
        return $this->boardRepository->isUserMember($boardId, $userId);
    }

    public function isUserAdminOrOwner(int $boardId, int $userId): bool
    {
        return $this->boardRepository->isUserAdminOrOwner($boardId, $userId);
    }
}
