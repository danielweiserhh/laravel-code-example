<?php

declare(strict_types=1);

namespace App\Application\Inbox\UseCases;

final class CreateInboxItemCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly ?int $workspaceId,
        public readonly string $content,
        public readonly string $source = 'manual'
    ) {}
}
