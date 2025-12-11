<?php

declare(strict_types=1);

namespace App\Application\Boards\UseCases;

use App\Domain\Boards\Repositories\ListRepositoryInterface;
use App\Domain\Boards\ValueObjects\BoardList;
use App\Models\ListModel;
use Illuminate\Support\Facades\DB;

final class UpdateListHandler
{
    public function __construct(
        private readonly ListRepositoryInterface $listRepository
    ) {}

    public function handle(UpdateListCommand $command): ListModel
    {
        return DB::transaction(function () use ($command) {
            $domainList = $this->listRepository->findOrFail($command->listId);

            $updatedDomainList = new BoardList(
                id: $domainList->id,
                boardId: $domainList->boardId,
                name: $command->name ?? $domainList->name,
                position: $command->position ?? $domainList->position,
                isArchived: $command->isArchived ?? $domainList->isArchived,
                createdAt: $domainList->createdAt,
                updatedAt: now()->toIso8601String()
            );

            $savedDomainList = $this->listRepository->save($updatedDomainList);

            return ListModel::findOrFail($savedDomainList->id);
        });
    }
}
