<?php

declare(strict_types=1);

namespace App\Application\AI\UseCases;

readonly class SuggestCardRewriteCommand
{
    /**
     * @param array<int, string> $fields
     */
    public function __construct(
        public int $userId,
        public int $cardId,
        public array $fields = ['title', 'description'],
        public string $language = 'ru',
    ) {}
}
