<?php

declare(strict_types=1);

namespace App\Application\Boards\Queries;

use App\Models\Card;
use Illuminate\Support\Collection;

final class GetCardsHandler
{
    
    public function handle(GetCardsQuery $query): Collection
    {
        $queryBuilder = Card::with(['assignees', 'checklists.items', 'comments']);

        if ($query->listId !== null) {
            $queryBuilder->where('list_id', $query->listId);
        } elseif ($query->boardId !== null) {
            $queryBuilder->where('board_id', $query->boardId);
        }

        
        $queryBuilder->where('is_completed', false);

        return $queryBuilder->orderBy('position')->get();
    }
}
