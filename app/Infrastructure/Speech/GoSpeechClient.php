<?php

declare(strict_types=1);

namespace App\Infrastructure\Speech;

use App\Domain\Speech\SpeechTranscriberInterface;
use Illuminate\Http\Client\Factory as HttpClient;
use Psr\Log\LoggerInterface;

final class GoSpeechClient implements SpeechTranscriberInterface
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly LoggerInterface $logger,
        private readonly string $serviceUrl,
        private readonly int $timeout = 60
    ) {
    }

    /**
     * @return array{text: string, language: string}
     */
    public function transcribe(
        string $audioContent,
        string $fileName,
        string $language = 'auto',
        ?string $model = null
    ): array {
        $params = ['language' => $language];
        
        if ($model !== null) {
            $params['model'] = $model;
        }

        try {
            $response = $this->http
                ->timeout($this->timeout)
                ->attach('audio', $audioContent, $fileName)
                ->post("{$this->serviceUrl}/api/speech-to-text", $params);

            if ($response->failed()) {
                $this->logger->error('Speech recognition service error', [
                    'status_code' => $response->status(),
                    'language' => $language,
                ]);

                throw new \RuntimeException('Сервис распознавания речи недоступен', 503);
            }

            $data = $response->json();

            $this->logger->info('Speech recognition completed', [
                'language' => $data['language'] ?? $language,
                'text_length' => mb_strlen($data['text'] ?? ''),
            ]);

            return [
                'text' => $data['text'] ?? '',
                'language' => $data['language'] ?? $language,
            ];
        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Speech recognition error', [
                'error' => $e->getMessage(),
                'language' => $language,
            ]);

            throw new \RuntimeException('Ошибка распознавания речи: '.$e->getMessage(), 503, $e);
        }
    }
}
