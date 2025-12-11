<?php

declare(strict_types=1);

namespace App\Domain\FocusSessions\Enums;

enum FocusSessionStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case PAUSED = 'paused';
    case COMPLETED = 'completed';

    public function canTransitionTo(self $newStatus): bool
    {
        return match ($this) {
            self::PENDING => $newStatus === self::ACTIVE,
            self::ACTIVE => in_array($newStatus, [self::PAUSED, self::COMPLETED], true),
            self::PAUSED => in_array($newStatus, [self::ACTIVE, self::COMPLETED], true),
            self::COMPLETED => false,
        };
    }
}
