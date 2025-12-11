<?php

declare(strict_types=1);

namespace App\Application\Workspaces\UseCases;

readonly class DeleteWorkspaceCommand
{
    public function __construct(
        public int $workspaceId,
    ) {}
}
