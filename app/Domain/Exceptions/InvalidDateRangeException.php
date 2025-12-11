<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use DateTimeImmutable;

class InvalidDateRangeException extends DomainException
{
    public function __construct(
        ?DateTimeImmutable $startDate,
        ?DateTimeImmutable $dueDate,
        ?string $message = null
    ) {
        $message ??= 'Дата окончания не может быть раньше даты начала';

        parent::__construct($message, [
            'start_date' => $startDate?->format('c'),
            'due_date' => $dueDate?->format('c'),
        ]);
    }

    public function getUserMessage(): string
    {
        return 'Дата окончания не может быть раньше даты начала.';
    }
}
