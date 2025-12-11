<?php

declare(strict_types=1);

namespace App\Application\Users\UseCases;

final class UpdateProfileCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly ?string $name = null,
        public readonly ?string $phone = null,
        public readonly ?string $telegramUsername = null,
        public readonly ?string $aiModel = null,
        public readonly ?string $speechModel = null
    ) {}
}
