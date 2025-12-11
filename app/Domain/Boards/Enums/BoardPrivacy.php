<?php

declare(strict_types=1);

namespace App\Domain\Boards\Enums;

enum BoardPrivacy: string
{
    case PRIVATE = 'private';
    case WORKSPACE = 'workspace';
    case PUBLIC = 'public';

    public static function default(): self
    {
        return self::PRIVATE;
    }
}
