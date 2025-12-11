<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

class InvalidContentException extends DomainException
{
    public function __construct(
        string $message = 'Content is invalid',
        array $context = []
    ) {
        parent::__construct($message, $context);
    }

    public function getUserMessage(): string
    {
        return $this->getMessage();
    }
}
