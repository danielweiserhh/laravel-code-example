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

final class FormatCardHandler
{
    public function __construct(
        private readonly AIJobRepositoryInterface $jobs,
        private readonly LoggerInterface $logger
    ) {}

    public function handle(FormatCardCommand $command): AIJob
    {
        $payload = [
            'title' => $command->title,
            'description' => $command->description,
            'language' => $command->language,
        ];

        if ($command->model !== null) {
            $payload['model'] = $command->model;
        }

        $job = $this->jobs->create([
            'id' => (string) Str::uuid(),
            'user_id' => $command->userId,
            'workspace_id' => $command->workspaceId,
            'type' => AIJobType::CARD_FORMAT,
            'payload' => $payload,
            'status' => JobStatus::PENDING,
        ]);

        $this->logger->info('AI card format job created', [
            'job_id' => $job->id,
            'user_id' => $command->userId,
        ]);

        SendAIJobToQueue::dispatch($job)->onQueue('default');

        return $job;
    }
}
