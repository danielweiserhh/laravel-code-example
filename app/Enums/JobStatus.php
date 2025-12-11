<?php

declare(strict_types=1);

namespace App\Enums;

enum JobStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::SUCCEEDED => 'Succeeded',
            self::FAILED => 'Failed',
        };
    }
}
