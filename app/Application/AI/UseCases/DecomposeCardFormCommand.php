<?php

declare(strict_types=1);

namespace App\Application\AI\UseCases;

readonly class DecomposeCardFormCommand
{
    public function __construct(
        public int $userId,
        public ?int $workspaceId,
        public string $title,
        public string $description,
        public string $language = 'ru',
        public ?string $model = null,
    ) {}
}
