<?php

declare(strict_types=1);

namespace Tests\Unit\Application\AI;

use App\Application\AI\UseCases\AssistWithTaskCommand;
use App\Application\AI\UseCases\AssistWithTaskHandler;
use App\Domain\AI\Repositories\AIJobRepositoryInterface;
use App\Enums\AIJobType;
use App\Enums\JobStatus;
use App\Models\AIJob;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

class AssistWithTaskHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    public function test_creates_assist_job(): void
    {
        $expectedJob = new AIJob;
        $expectedJob->id = 'test-uuid';
        $expectedJob->user_id = 1;
        $expectedJob->workspace_id = 2;
        $expectedJob->type = AIJobType::ASSIST_WITH_TASK;
        $expectedJob->status = JobStatus::PENDING;
        $expectedJob->payload = [
            'assist_type' => 'start_task',
            'card_id' => 10,
            'context' => 'Test context',
            'language' => 'ru',
        ];

        $repo = Mockery::mock(AIJobRepositoryInterface::class);
        $repo->shouldReceive('create')
            ->once()
            ->withArgs(function (array $data) {
                return $data['user_id'] === 1
                    && $data['workspace_id'] === 2
                    && $data['type'] === AIJobType::ASSIST_WITH_TASK
                    && $data['payload']['assist_type'] === 'start_task'
                    && $data['payload']['card_id'] === 10;
            })
            ->andReturn($expectedJob);

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info')->once();

        $handler = new AssistWithTaskHandler($repo, $logger);
        $command = new AssistWithTaskCommand(
            userId: 1,
            workspaceId: 2,
            assistType: 'start_task',
            cardId: 10,
            contextText: 'Test context',
            language: 'ru'
        );

        $result = $handler->handle($command);

        $this->assertSame('test-uuid', $result->id);
        $this->assertSame(1, $result->user_id);
    }

    public function test_creates_assist_job_without_card(): void
    {
        $expectedJob = new AIJob;
        $expectedJob->id = 'test-uuid-2';
        $expectedJob->user_id = 1;
        $expectedJob->workspace_id = null;
        $expectedJob->type = AIJobType::ASSIST_WITH_TASK;
        $expectedJob->status = JobStatus::PENDING;

        $repo = Mockery::mock(AIJobRepositoryInterface::class);
        $repo->shouldReceive('create')
            ->once()
            ->withArgs(function (array $data) {
                return $data['user_id'] === 1
                    && $data['workspace_id'] === null
                    && $data['payload']['card_id'] === null;
            })
            ->andReturn($expectedJob);

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info')->once();

        $handler = new AssistWithTaskHandler($repo, $logger);
        $command = new AssistWithTaskCommand(
            userId: 1,
            workspaceId: null,
            assistType: 'general_help',
            cardId: null,
            contextText: null,
            language: 'en'
        );

        $result = $handler->handle($command);

        $this->assertSame('test-uuid-2', $result->id);
    }
}
