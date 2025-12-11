<?php

declare(strict_types=1);

namespace App\Domain\Boards\Services;

use App\Domain\Boards\Enums\BoardPrivacy;
use App\Domain\Boards\Repositories\BoardRepositoryInterface;

final class BoardDomainService
{
    public function __construct(
        private readonly BoardRepositoryInterface $boardRepository
    ) {}

    public function validateName(string $name): void
    {
        if (empty(trim($name))) {
            throw new \DomainException('Название доски не может быть пустым');
        }

        if (mb_strlen($name) > 255) {
            throw new \DomainException('Название доски не может быть длиннее 255 символов');
        }
    }

    public function getDefaultPrivacy(): BoardPrivacy
    {
        return BoardPrivacy::PRIVATE;
    }

    public function canUserAccess(int $boardId, int $userId, BoardPrivacy $privacy): bool
    {
        if ($privacy === BoardPrivacy::PUBLIC) {
            return true;
        }

        return $this->boardRepository->isUserMember($boardId, $userId);
    }

    public function canUserModify(int $boardId, int $userId): bool
    {
        return $this->boardRepository->isUserAdminOrOwner($boardId, $userId);
    }
}
