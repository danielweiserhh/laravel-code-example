<?php

declare(strict_types=1);

namespace App\Application\Boards\Queries;

use App\Models\Board;
use Illuminate\Support\Collection;

final class GetBoardsHandler
{
    public function handle(GetBoardsQuery $query): Collection
    {
        $queryBuilder = Board::with(['workspace', 'members', 'lists']);

        if ($query->workspaceId !== null) {
            $queryBuilder->where('workspace_id', $query->workspaceId);
        } else {
            $queryBuilder->whereHas('members', function ($q) use ($query) {
                $q->where('user_id', $query->userId);
            });
        }

        return $queryBuilder->get();
    }
}
