<?php

declare(strict_types=1);

namespace App\Application\Users\UseCases;

final class UpdatePasswordCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly string $password
    ) {}
}
