<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

class InvalidStatusTransitionException extends DomainException
{
    public function __construct(
        string $currentStatus,
        string $newStatus,
        ?string $message = null
    ) {
        $message ??= sprintf(
            'Cannot transition from status "%s" to "%s"',
            $currentStatus,
            $newStatus
        );

        parent::__construct($message, [
            'current_status' => $currentStatus,
            'new_status' => $newStatus,
        ]);
    }

    public function getUserMessage(): string
    {
        return sprintf(
            'Невозможно перевести из статуса "%s" в "%s"',
            $this->getContext()['current_status'],
            $this->getContext()['new_status']
        );
    }
}
