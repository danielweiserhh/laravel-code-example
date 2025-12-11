<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AISuggestHappyTest extends TestCase
{
    use RefreshDatabase;

    public function test_card_format_returns_accepted_with_job_id(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $this->actingAs($user);

        $resp = $this->postJson('/api/v1/ai/suggest', [
            'context_type' => 'card_format',
            'options' => [
                'title' => 'заголовок',
                'description' => 'описание',
                'language' => 'ru',
            ],
        ]);
        $resp->assertStatus(202);
        $this->assertNotEmpty($resp->json('data.job_id'));
    }
}
