<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

abstract class DomainException extends \DomainException
{
    public function __construct(
        string $message,
        protected readonly array $context = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getUserMessage(): string
    {
        return $this->getMessage();
    }
}
