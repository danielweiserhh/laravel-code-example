<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;


readonly class UserId
{
    public function __construct(
        public int $value
    ) {
        if ($value <= 0) {
            throw new \InvalidArgumentException('User ID must be positive');
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
