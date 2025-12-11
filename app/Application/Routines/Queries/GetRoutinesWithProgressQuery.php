<?php

declare(strict_types=1);

namespace App\Application\Routines\Queries;


final readonly class GetRoutinesWithProgressQuery
{
    public function __construct(
        public int $userId,
        public ?string $date = null
    ) {}
}
