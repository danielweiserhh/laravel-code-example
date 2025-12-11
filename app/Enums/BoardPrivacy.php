<?php

declare(strict_types=1);

namespace App\Enums;

enum BoardPrivacy: string
{
    case PRIVATE = 'private';
    case WORKSPACE = 'workspace';
    case PUBLIC = 'public';

    public function label(): string
    {
        return match ($this) {
            self::PRIVATE => 'Private',
            self::WORKSPACE => 'Workspace',
            self::PUBLIC => 'Public',
        };
    }
}
