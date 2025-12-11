<?php

declare(strict_types=1);

namespace App\Enums;

enum FocusSessionStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case PAUSED = 'paused';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::ACTIVE => 'Active',
            self::COMPLETED => 'Completed',
            self::PAUSED => 'Paused',
        };
    }
}
