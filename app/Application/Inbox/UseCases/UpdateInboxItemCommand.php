<?php

declare(strict_types=1);

namespace App\Application\Inbox\UseCases;

readonly class UpdateInboxItemCommand
{
    public function __construct(
        public int $inboxItemId,
        public ?string $content = null,
        public ?bool $isProcessed = null,
    ) {}
}
