<?php

declare(strict_types=1);

namespace App\Enums;

enum TaskType: string
{
    case TASK = 'task';
    case BUG = 'bug';
    case RESEARCH = 'research';
    case ROUTINE = 'routine';

    public function label(): string
    {
        return match ($this) {
            self::TASK => 'Task',
            self::BUG => 'Bug',
            self::RESEARCH => 'Research',
            self::ROUTINE => 'Routine',
        };
    }
}
