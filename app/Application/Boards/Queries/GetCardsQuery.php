<?php

declare(strict_types=1);

namespace App\Application\Boards\Queries;

final class GetCardsQuery
{
    public function __construct(
        public readonly ?int $listId = null,
        public readonly ?int $boardId = null,
    ) {}
}
