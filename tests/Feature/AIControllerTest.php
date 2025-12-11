<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Card;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AIControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Queue::fake();
    }

    public function test_can_decompose_card(): void
    {
        $workspace = Workspace::factory()->create();
        $board = Board::factory()->create(['workspace_id' => $workspace->id]);
        $card = Card::factory()->create(['board_id' => $board->id]);
        $board->members()->attach($this->user->id);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/cards/'.$card->id.'/ai/decompose', [
                'language' => 'ru',
            ]);

        $response->assertStatus(202)
            ->assertJsonStructure(['success', 'data' => ['job_id'], 'message']);

        Queue::assertPushed(\App\Jobs\SendAIJobToQueue::class);
    }

    public function test_can_assist_with_task(): void
    {
        $workspace = Workspace::factory()->create();
        $workspace->members()->attach($this->user->id);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/ai/assist', [
                'workspace_id' => $workspace->id,
                'assist_type' => 'start_task',
                'language' => 'ru',
            ]);

        $response->assertStatus(202)
            ->assertJsonStructure(['success', 'data' => ['job_id'], 'message']);

        Queue::assertPushed(\App\Jobs\SendAIJobToQueue::class);
    }

    public function test_can_get_job_status(): void
    {
        $aiJob = \App\Models\AIJob::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/ai/jobs/'.$aiJob->id);

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'data' => ['id' => $aiJob->id]]);
    }
}
