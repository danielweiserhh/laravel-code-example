<?php

declare(strict_types=1);

namespace App\Application\Inbox\UseCases;

readonly class DeleteInboxItemCommand
{
    public function __construct(
        public int $inboxItemId,
    ) {}
}
