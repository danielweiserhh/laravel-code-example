<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Jobs\SendAIJobToQueue;
use App\Models\Board;
use App\Models\Card;
use App\Models\InboxItem;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AISuggestTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_inbox_suggestion(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $inboxItem = InboxItem::factory()->for($user)->create();

        $response = $this->actingAs($user)->postJson('/api/v1/ai/suggest', [
            'context_type' => 'inbox_item',
            'context_id' => $inboxItem->id,
        ]);

        $response->assertStatus(202)->assertJsonStructure(['success', 'data' => ['job_id', 'status']]);

        Queue::assertPushed(SendAIJobToQueue::class, function (SendAIJobToQueue $job) use ($response) {
            return $job->aiJob->id === $response->json('data.job_id');
        });

        $this->assertDatabaseHas('ai_jobs', [
            'id' => $response->json('data.job_id'),
            'type' => 'PARSE_INBOX_ITEM',
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_request_today_plan_suggestion(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        Workspace::factory()->hasAttached($user, [], 'members')->create();

        $response = $this->actingAs($user)->postJson('/api/v1/ai/suggest', [
            'context_type' => 'today_plan',
        ]);

        $response->assertStatus(202)->assertJsonStructure(['success', 'data' => ['job_id', 'status']]);

        $this->assertDatabaseHas('ai_jobs', [
            'id' => $response->json('data.job_id'),
            'type' => 'GENERATE_TODAY_PLAN',
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_request_card_steps_suggestion(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $board = Board::factory()->for($workspace)->create();
        $board->members()->attach($user->id, ['role' => 'member']);
        $card = Card::factory()->for($board)->create();

        $response = $this->actingAs($user)->postJson('/api/v1/ai/suggest', [
            'context_type' => 'card_steps',
            'context_id' => $card->id,
        ]);

        $response->assertStatus(202)->assertJsonStructure(['success', 'data' => ['job_id', 'status']]);

        $this->assertDatabaseHas('ai_jobs', [
            'id' => $response->json('data.job_id'),
            'type' => 'DECOMPOSE_CARD',
            'user_id' => $user->id,
        ]);
    }

    public function test_validation_fails_without_context_id_when_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/ai/suggest', [
            'context_type' => 'card_steps',
        ]);

        $response->assertStatus(422);
    }
}
