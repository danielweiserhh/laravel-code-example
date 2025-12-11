<?php

declare(strict_types=1);

namespace App\Enums;

enum CustomFieldType: string
{
    case TEXT = 'text';
    case NUMBER = 'number';
    case DATE = 'date';
    case SELECT = 'select';
    case CHECKBOX = 'checkbox';

    public function label(): string
    {
        return match ($this) {
            self::TEXT => 'Text',
            self::NUMBER => 'Number',
            self::DATE => 'Date',
            self::SELECT => 'Select',
            self::CHECKBOX => 'Checkbox',
        };
    }
}
