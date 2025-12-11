<?php

declare(strict_types=1);

namespace App\Domain\Boards\ValueObjects;

readonly class CardPositionData
{
    public function __construct(
        public int $id,
        public int $position
    ) {}
}
