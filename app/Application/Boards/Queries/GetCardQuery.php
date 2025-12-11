<?php

declare(strict_types=1);

namespace App\Application\Boards\Queries;

final readonly class GetCardQuery
{
    public function __construct(
        public int $cardId
    ) {}
}
