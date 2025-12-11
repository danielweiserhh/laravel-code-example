<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Domain\Speech\SpeechTranscriberInterface;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

class VoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_transcribe_audio(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $audioFile = UploadedFile::fake()->create('audio.wav', 100);

        $this->mock(SpeechTranscriberInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('transcribe')
                ->once()
                ->andReturn([
                    'text' => 'Test transcription',
                    'language' => 'en',
                ]);
        });

        $response = $this->actingAs($user)->postJson('/api/v1/voice/transcribe', [
            'audio' => $audioFile,
            'language' => 'en',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'text' => 'Test transcription',
                    'language' => 'en',
                ],
            ]);
    }

    public function test_transcription_handles_service_error(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $audioFile = UploadedFile::fake()->create('audio.wav', 100);

        $this->mock(SpeechTranscriberInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('transcribe')
                ->once()
                ->andThrow(new \RuntimeException('Transcription service unavailable', 503));
        });

        $response = $this->actingAs($user)->postJson('/api/v1/voice/transcribe', [
            'audio' => $audioFile,
        ]);

        $response->assertStatus(500);
    }

    public function test_unauthenticated_user_cannot_transcribe(): void
    {
        $audioFile = UploadedFile::fake()->create('audio.wav', 100);

        $response = $this->postJson('/api/v1/voice/transcribe', [
            'audio' => $audioFile,
        ]);

        $response->assertStatus(401);
    }

    public function test_validation_requires_audio_file(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/voice/transcribe', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonValidationErrors(['audio']);
    }
}
