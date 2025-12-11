<?php

declare(strict_types=1);

namespace App\Domain\Boards\Repositories;

use App\Domain\Boards\ValueObjects\Board as DomainBoard;
use App\DTOs\BoardCreateDTO;
use App\DTOs\BoardUpdateDTO;

interface BoardRepositoryInterface
{
    public function find(int $id): ?DomainBoard;

    public function findOrFail(int $id): DomainBoard;

    public function findWithRelations(int $id, array $relations): ?DomainBoard;

    public function create(BoardCreateDTO $dto): DomainBoard;

    public function update(int $id, BoardUpdateDTO $dto): DomainBoard;

    public function save(DomainBoard $board): DomainBoard;

    public function delete(int $id): bool;

    public function isUserMember(int $boardId, int $userId): bool;

    public function isUserAdminOrOwner(int $boardId, int $userId): bool;

    public function findByNameInUserWorkspaces(int $userId, string $boardName): ?DomainBoard;
}
