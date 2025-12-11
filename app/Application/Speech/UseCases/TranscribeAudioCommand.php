<?php

declare(strict_types=1);

namespace App\Application\Speech\UseCases;


readonly class TranscribeAudioCommand
{
    public function __construct(
        public int $userId,
        public string $audioContent,
        public string $fileName,
        public string $language = 'auto',
        public ?string $model = null,
    ) {}
}
