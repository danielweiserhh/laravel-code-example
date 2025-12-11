<?php

declare(strict_types=1);

namespace App\Domain\Speech;

interface SpeechTranscriberInterface
{
    public function transcribe(
        string $audioContent,
        string $fileName,
        string $language = 'auto',
        ?string $model = null
    ): array;
}
