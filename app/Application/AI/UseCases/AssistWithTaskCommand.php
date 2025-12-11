<?php

declare(strict_types=1);

namespace App\Application\AI\UseCases;

readonly class AssistWithTaskCommand
{
    public function __construct(
        public int $userId,
        public ?int $workspaceId,
        public string $assistType,
        public ?int $cardId = null,
        public ?string $contextText = null,
        public string $language = 'ru',
    ) {}
}
