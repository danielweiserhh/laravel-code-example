<?php

declare(strict_types=1);

namespace App\Domain\Routines\Enums;

enum RoutineType: string
{
    case MORNING = 'morning';
    case EVENING = 'evening';
    case WORK = 'work';
    case CUSTOM = 'custom';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
