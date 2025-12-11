<?php

declare(strict_types=1);

namespace App\Application\Boards\UseCases;

use App\Domain\Boards\Repositories\ListRepositoryInterface;
use App\Models\ListModel;
use Illuminate\Support\Facades\DB;

final class CreateListHandler
{
    public function __construct(
        private readonly ListRepositoryInterface $listRepository
    ) {}

    public function handle(CreateListCommand $command): ListModel
    {
        return DB::transaction(function () use ($command) {
            $position = $command->position;

            if ($position === null) {
                $maxPosition = $this->listRepository->getMaxPositionForBoard($command->boardId);
                $position = $maxPosition + 1;
            }

            $domainList = $this->listRepository->create(
                boardId: $command->boardId,
                name: $command->name,
                position: $position
            );

            return ListModel::findOrFail($domainList->id);
        });
    }
}
