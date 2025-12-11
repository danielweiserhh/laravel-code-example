<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

class InvalidDurationException extends DomainException
{
    public function __construct(
        int $duration,
        int $min,
        int $max,
        ?string $message = null
    ) {
        $message ??= sprintf(
            'Duration %d is invalid. Must be between %d and %d',
            $duration,
            $min,
            $max
        );

        parent::__construct($message, [
            'duration' => $duration,
            'min' => $min,
            'max' => $max,
        ]);
    }

    public function getUserMessage(): string
    {
        return $this->getMessage();
    }
}
