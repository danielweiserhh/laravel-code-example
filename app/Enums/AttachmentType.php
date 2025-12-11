<?php

declare(strict_types=1);

namespace App\Enums;

enum AttachmentType: string
{
    case FILE = 'file';
    case IMAGE = 'image';
    case LINK = 'link';

    public function label(): string
    {
        return match ($this) {
            self::FILE => 'File',
            self::IMAGE => 'Image',
            self::LINK => 'Link',
        };
    }
}
