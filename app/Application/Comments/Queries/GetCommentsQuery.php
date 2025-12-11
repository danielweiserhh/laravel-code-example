<?php

declare(strict_types=1);

namespace App\Application\Comments\Queries;

final class GetCommentsQuery
{
    public function __construct(
        public readonly int $cardId
    ) {}
}
