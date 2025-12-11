<?php

declare(strict_types=1);

namespace Tests\Unit\Application\AI;

use App\Application\AI\UseCases\CreateAIJobCommand;
use App\Application\AI\UseCases\CreateAIJobHandler;
use App\Domain\AI\Repositories\AIJobRepositoryInterface;
use App\Enums\AIJobType;
use App\Models\AIJob;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Mockery;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

class CreateAIJobHandlerTest extends TestCase
{
    public function test_handles_valid_command_and_saves_job(): void
    {
        Queue::fake();

        app()->instance('request_id', 'test-request-id');

        $expectedJob = new AIJob;
        $expectedJob->id = Str::uuid()->toString();
        $expectedJob->user_id = 1;
        $expectedJob->workspace_id = 2;
        $expectedJob->type = AIJobType::CARD_FORMAT;
        $expectedJob->status = \App\Enums\JobStatus::PENDING;
        $expectedJob->payload = ['title' => 't', 'description' => 'd', 'language' => 'ru'];

        $repo = Mockery::mock(AIJobRepositoryInterface::class);
        $repo->shouldReceive('create')->once()->with(\Mockery::on(function (array $data) {
            return $data['user_id'] === 1
                && $data['workspace_id'] === 2
                && $data['type'] === AIJobType::CARD_FORMAT
                && $data['status'] === \App\Enums\JobStatus::PENDING
                && $data['payload']['title'] === 't';
        }))->andReturn($expectedJob);

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info')->once();

        $handler = new CreateAIJobHandler($repo, $logger);

        $cmd = new CreateAIJobCommand(
            userId: 1,
            workspaceId: 2,
            type: AIJobType::CARD_FORMAT->value,
            payload: ['title' => 't', 'description' => 'd', 'language' => 'ru']
        );

        $job = $handler->handle($cmd);

        $this->assertInstanceOf(AIJob::class, $job);
        $this->assertSame(1, $job->user_id);
        $this->assertSame(2, $job->workspace_id);
        $this->assertSame(AIJobType::CARD_FORMAT, $job->type);
        $this->assertSame('pending', $job->status->value);
    }
}
