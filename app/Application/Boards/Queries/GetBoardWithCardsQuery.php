<?php

declare(strict_types=1);

namespace App\Application\Boards\Queries;

final class GetBoardWithCardsQuery
{
    public function __construct(
        public readonly int $boardId
    ) {}
}
