<?php

declare(strict_types=1);

namespace App\Application\AI\UseCases;

final class ParseInboxItemCommand
{
    public function __construct(
        public readonly int $inboxItemId,
        public readonly string $language = 'ru',
    ) {}
}
