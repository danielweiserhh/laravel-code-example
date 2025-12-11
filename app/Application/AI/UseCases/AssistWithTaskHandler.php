<?php

declare(strict_types=1);

namespace App\Application\AI\UseCases;

use App\Domain\AI\Repositories\AIJobRepositoryInterface;
use App\Enums\AIJobType;
use Psr\Log\LoggerInterface;
use App\Enums\JobStatus;
use App\Jobs\SendAIJobToQueue;
use App\Models\AIJob;
use Illuminate\Support\Str;

final class AssistWithTaskHandler
{
    public function __construct(
        private readonly AIJobRepositoryInterface $jobs,
        private readonly LoggerInterface $logger
    ) {}

    public function handle(AssistWithTaskCommand $command): AIJob
    {
        $job = $this->jobs->create([
            'id' => (string) Str::uuid(),
            'user_id' => $command->userId,
            'workspace_id' => $command->workspaceId,
            'type' => AIJobType::ASSIST_WITH_TASK,
            'payload' => [
                'assist_type' => $command->assistType,
                'card_id' => $command->cardId,
                'context' => $command->contextText,
                'language' => $command->language,
            ],
            'status' => JobStatus::PENDING,
        ]);

        $this->logger->info('AI job created for task assistance', [
            'job_id' => $job->id,
            'assist_type' => $command->assistType,
            'user_id' => $command->userId,
        ]);

        SendAIJobToQueue::dispatch($job)->onQueue('default');

        return $job;
    }
}
