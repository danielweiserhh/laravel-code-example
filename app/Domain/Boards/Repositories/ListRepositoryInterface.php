<?php

declare(strict_types=1);

namespace App\Domain\Boards\Repositories;

use App\Domain\Boards\ValueObjects\BoardList;

interface ListRepositoryInterface
{
    public function find(int $id): ?BoardList;

    public function findOrFail(int $id): BoardList;

    public function getByBoard(int $boardId): array;

    public function getMaxPositionForBoard(int $boardId): int;

    public function create(int $boardId, string $name, int $position): BoardList;

    public function save(BoardList $list): BoardList;

    public function delete(int $id): bool;
}
