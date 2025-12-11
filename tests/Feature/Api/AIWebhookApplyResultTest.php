<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\AIJobType;
use App\Enums\JobStatus;
use App\Events\AIJobCompleted;
use App\Models\AIJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AIWebhookApplyResultTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_applies_result_and_marks_succeeded(): void
    {
        Event::fake([AIJobCompleted::class]);

        $user = User::factory()->create();
        $job = AIJob::create([
            'user_id' => $user->id,
            'workspace_id' => null,
            'type' => AIJobType::CARD_FORMAT,
            'payload' => ['title' => 'raw'],
            'status' => JobStatus::PROCESSING,
        ]);

        $payload = [
            'job_id' => $job->id,
            'type' => 'CARD_FORMAT',
            'result' => ['title' => 'T', 'description' => 'D'],
        ];

        $resp = $this->postJson('/api/v1/webhooks/ai-job-completed', $payload, [
            'X-Internal-Service' => 'go-ai',
        ]);
        $resp->assertStatus(200);

        $job->refresh();
        $this->assertEquals(JobStatus::SUCCEEDED, $job->status);
        $this->assertEquals('T', $job->result['title'] ?? null);
    }

    public function test_webhook_returns_not_found_when_job_does_not_exist(): void
    {
        $payload = [
            'job_id' => '00000000-0000-0000-0000-000000000000',
            'type' => AIJobType::CARD_FORMAT->value,
            'status' => 'succeeded',
            'result' => [],
        ];

        $response = $this->postJson('/api/v1/webhooks/ai-job-completed', $payload, [
            'X-Internal-Service' => 'go-ai',
        ]);

        $response->assertStatus(404);
    }

    public function test_webhook_marks_job_as_failed_when_status_failed(): void
    {
        Event::fake([AIJobCompleted::class]);

        $user = User::factory()->create();
        $job = AIJob::create([
            'user_id' => $user->id,
            'workspace_id' => null,
            'type' => AIJobType::CARD_FORMAT,
            'payload' => ['title' => 'raw'],
            'status' => JobStatus::PROCESSING,
        ]);

        $payload = [
            'job_id' => $job->id,
            'type' => AIJobType::CARD_FORMAT->value,
            'status' => 'failed',
            'error_message' => 'Something went wrong in AI service',
        ];

        $response = $this->postJson('/api/v1/webhooks/ai-job-completed', $payload, [
            'X-Internal-Service' => 'go-ai',
        ]);

        $response->assertSuccessful();

        $job->refresh();
        $this->assertEquals(JobStatus::FAILED, $job->status);
        $this->assertSame('Something went wrong in AI service', $job->error_message);

        Event::assertDispatched(AIJobCompleted::class, function (AIJobCompleted $event) use ($job) {
            return $event->aiJob->id === $job->id;
        });
    }
}
