<?php

declare(strict_types=1);

namespace App\Domain\Routines\Services;

use App\Domain\Exceptions\InvalidDurationException;
use App\Domain\Routines\ValueObjects\Routine;

final class RoutineDomainService
{
    private const MAX_NAME_LENGTH = 255;
    private const MAX_DESCRIPTION_LENGTH = 5000;
    private const VALID_TYPES = ['morning', 'evening', 'work', 'custom'];

    public function validateName(string $name): void
    {
        if (empty(trim($name))) {
            throw new \DomainException('Название рутины не может быть пустым');
        }

        if (mb_strlen($name) > self::MAX_NAME_LENGTH) {
            throw new \DomainException('Название рутины слишком длинное (максимум 255 символов)');
        }
    }

    public function validateDescription(?string $description): void
    {
        if ($description !== null && mb_strlen($description) > self::MAX_DESCRIPTION_LENGTH) {
            throw new \DomainException('Описание рутины слишком длинное (максимум 5000 символов)');
        }
    }

    public function validateType(string $type): void
    {
        if (! in_array($type, self::VALID_TYPES, true)) {
            throw new \DomainException(
                sprintf('Недопустимый тип рутины. Допустимые: %s', implode(', ', self::VALID_TYPES))
            );
        }
    }

    public function canBeActivated(Routine $routine): bool
    {
        return ! empty(trim($routine->name));
    }

    public function canBeDeactivated(Routine $routine): bool
    {
        return $routine->isActive;
    }

    public function validateStepTitle(string $title): void
    {
        if (empty(trim($title))) {
            throw new \DomainException('Название шага не может быть пустым');
        }

        if (mb_strlen($title) > self::MAX_NAME_LENGTH) {
            throw new \DomainException('Название шага слишком длинное (максимум 255 символов)');
        }
    }

    public function validateStepDuration(?int $durationMinutes): void
    {
        if ($durationMinutes === null) {
            return;
        }

        if ($durationMinutes < 1 || $durationMinutes > 480) {
            throw new InvalidDurationException($durationMinutes, 1, 480);
        }
    }

    public function getValidTypes(): array
    {
        return self::VALID_TYPES;
    }
}
