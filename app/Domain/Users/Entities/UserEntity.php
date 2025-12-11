<?php

declare(strict_types=1);

namespace App\Domain\Users\Entities;

use App\Domain\Exceptions\InvariantViolationException;
use DateTimeImmutable;

final class UserEntity
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $password,
        public readonly ?string $googleId,
        public readonly ?string $avatar,
        public readonly ?string $phone,
        public readonly ?string $telegramUsername,
        public readonly ?string $aiModel,
        public readonly ?string $speechModel,
        public readonly ?DateTimeImmutable $emailVerifiedAt,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->id <= 0) {
            throw new InvariantViolationException(
                'user.id.positive',
                'User ID must be positive',
                ['id' => $this->id]
            );
        }

        if (trim($this->name) === '') {
            throw new InvariantViolationException(
                'user.name.not_empty',
                'User name cannot be empty',
                ['name' => $this->name]
            );
        }

        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvariantViolationException(
                'user.email.invalid',
                'User email must be valid',
                ['email' => $this->email]
            );
        }
    }
}
