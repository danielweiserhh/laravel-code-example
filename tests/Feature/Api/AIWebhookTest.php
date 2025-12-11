<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\AIJobType;
use App\Enums\JobStatus;
use App\Events\AIJobCompleted;
use App\Models\AIJob;
use App\Models\InboxItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tests\TestCase;

class AIWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_requires_internal_header(): void
    {
        $response = $this->postJson('/api/v1/webhooks/ai-job-completed', []);

        $response->assertStatus(403);
    }

    public function test_webhook_updates_inbox_suggestions_and_dispatches_event(): void
    {
        Event::fake([AIJobCompleted::class]);

        $user = User::factory()->create();
        $inboxItem = InboxItem::factory()->for($user)->create();

        $aiJob = AIJob::factory()->create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'workspace_id' => $inboxItem->workspace_id,
            'type' => AIJobType::PARSE_INBOX_ITEM,
            'status' => JobStatus::PROCESSING,
            'payload' => [
                'inbox_item_id' => $inboxItem->id,
            ],
        ]);

        $result = [
            'title' => 'Позвонить клиенту',
            'due_date' => '2025-12-01T10:00:00',
            'duration_estimate' => 15,
            'suggested_board' => 'Продажи',
            'suggested_list' => 'Звонки',
        ];

        $response = $this->withHeaders([
            'X-Internal-Service' => 'go-ai',
        ])->postJson('/api/v1/webhooks/ai-job-completed', [
            'job_id' => $aiJob->id,
            'type' => AIJobType::PARSE_INBOX_ITEM->value,
            'result' => $result,
        ]);

        $response->assertOk();

        $freshInbox = $inboxItem->fresh();
        $this->assertNotNull($freshInbox->ai_suggestions);
        $this->assertSame($result['title'], $freshInbox->ai_suggestions['title']);

        Event::assertDispatched(AIJobCompleted::class, function (AIJobCompleted $event) use ($aiJob) {
            return $event->aiJob->id === $aiJob->id;
        });
    }

    public function test_webhook_returns_404_for_missing_job(): void
    {
        $response = $this->withHeaders([
            'X-Internal-Service' => 'go-ai',
        ])->postJson('/api/v1/webhooks/ai-job-completed', [
            'job_id' => (string) Str::uuid(),
            'type' => AIJobType::PARSE_INBOX_ITEM->value,
            'result' => [],
        ]);

        $response->assertStatus(404);
    }
}
