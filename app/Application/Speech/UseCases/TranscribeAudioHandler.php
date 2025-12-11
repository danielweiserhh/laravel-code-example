<?php

declare(strict_types=1);

namespace App\Application\Speech\UseCases;

use App\Domain\Speech\SpeechTranscriberInterface;
use Psr\Log\LoggerInterface;

final class TranscribeAudioHandler
{
    public function __construct(
        private readonly SpeechTranscriberInterface $transcriber,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * @return array{text: string, language: string}
     */
    public function handle(TranscribeAudioCommand $command): array
    {
        $this->logger->info('Starting audio transcription', [
            'user_id' => $command->userId,
            'file_name' => $command->fileName,
            'language' => $command->language,
        ]);

        $result = $this->transcriber->transcribe(
            $command->audioContent,
            $command->fileName,
            $command->language,
            $command->model
        );

        $this->logger->info('Audio transcription completed', [
            'user_id' => $command->userId,
            'result_language' => $result['language'],
            'text_length' => mb_strlen($result['text']),
        ]);

        return $result;
    }
}
