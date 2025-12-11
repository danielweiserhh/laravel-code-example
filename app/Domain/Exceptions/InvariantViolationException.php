<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

class InvariantViolationException extends DomainException
{
    public function __construct(
        string $invariant,
        string $message,
        array $context = []
    ) {
        parent::__construct($message, array_merge(['invariant' => $invariant], $context));
    }

    public function getUserMessage(): string
    {
        return 'Операция невозможна из-за нарушения бизнес-правил.';
    }
}
