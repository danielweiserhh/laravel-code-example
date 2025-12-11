<?php

declare(strict_types=1);

namespace App\Application\Boards\UseCases;

final class UpdateChecklistCommand
{
    public function __construct(
        public readonly int $checklistId,
        public readonly ?string $title = null
    ) {}
}
