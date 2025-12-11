<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Speech;

use App\Application\Speech\UseCases\TranscribeAudioCommand;
use App\Application\Speech\UseCases\TranscribeAudioHandler;
use App\Domain\Speech\SpeechTranscriberInterface;
use Mockery;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

class TranscribeAudioHandlerTest extends TestCase
{
    public function test_transcribes_audio_successfully(): void
    {
        $transcriber = Mockery::mock(SpeechTranscriberInterface::class);
        $transcriber->shouldReceive('transcribe')
            ->once()
            ->with('audio-content', 'test.wav', 'auto', null)
            ->andReturn([
                'text' => 'Transcribed text',
                'language' => 'ru',
            ]);

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info')->twice();

        $handler = new TranscribeAudioHandler($transcriber, $logger);
        $command = new TranscribeAudioCommand(
            userId: 1,
            audioContent: 'audio-content',
            fileName: 'test.wav',
            language: 'auto',
            model: null
        );

        $result = $handler->handle($command);

        $this->assertSame('Transcribed text', $result['text']);
        $this->assertSame('ru', $result['language']);
    }

    public function test_transcribes_with_specific_language_and_model(): void
    {
        $transcriber = Mockery::mock(SpeechTranscriberInterface::class);
        $transcriber->shouldReceive('transcribe')
            ->once()
            ->with('audio-content', 'test.mp3', 'en', 'large')
            ->andReturn([
                'text' => 'English text',
                'language' => 'en',
            ]);

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info')->twice();

        $handler = new TranscribeAudioHandler($transcriber, $logger);
        $command = new TranscribeAudioCommand(
            userId: 1,
            audioContent: 'audio-content',
            fileName: 'test.mp3',
            language: 'en',
            model: 'large'
        );

        $result = $handler->handle($command);

        $this->assertSame('English text', $result['text']);
        $this->assertSame('en', $result['language']);
    }

    public function test_throws_exception_on_transcription_failure(): void
    {
        $transcriber = Mockery::mock(SpeechTranscriberInterface::class);
        $transcriber->shouldReceive('transcribe')
            ->once()
            ->andThrow(new \RuntimeException('Service unavailable', 503));

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info')->once();

        $handler = new TranscribeAudioHandler($transcriber, $logger);
        $command = new TranscribeAudioCommand(
            userId: 1,
            audioContent: 'audio-content',
            fileName: 'test.wav',
            language: 'auto',
            model: null
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Service unavailable');

        $handler->handle($command);
    }
}
