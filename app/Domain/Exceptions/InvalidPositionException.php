<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

class InvalidPositionException extends DomainException
{
    public function __construct(
        int $position,
        int $maxAllowed,
        ?string $entityType = 'item'
    ) {
        $message = sprintf(
            'Position %d is invalid for %s. Maximum allowed position is %d.',
            $position,
            $entityType,
            $maxAllowed
        );

        parent::__construct($message, [
            'position' => $position,
            'max_allowed' => $maxAllowed,
            'entity_type' => $entityType,
        ]);
    }

    public function getUserMessage(): string
    {
        return 'Указана недопустимая позиция элемента.';
    }
}
