<?php

declare(strict_types=1);

namespace App\Application\Boards\Queries;

use App\Domain\Boards\Repositories\BoardRepositoryInterface;
use App\Models\Board;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class GetBoardWithCardsHandler
{
    public function __construct(
        private readonly BoardRepositoryInterface $boardRepository
    ) {}

    public function handle(GetBoardWithCardsQuery $query): Board
    {
        $relations = [
            'workspace.members',
            'members',
            'lists' => function ($q) {
                $q->orderBy('position');
            },
            'lists.cards' => function ($q) {
                $q->orderBy('position');
            },
            'customFields',
        ];
        
        $domainBoard = $this->boardRepository->findWithRelations(
            $query->boardId,
            $relations
        );

        if (! $domainBoard) {
            throw new ModelNotFoundException('Board not found');
        }

        return Board::with($relations)->findOrFail($domainBoard->id);
    }
}
