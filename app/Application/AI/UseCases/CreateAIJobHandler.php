<?php

declare(strict_types=1);

namespace App\Application\AI\UseCases;

use App\Domain\AI\Repositories\AIJobRepositoryInterface;
use App\Enums\AIJobType;
use Psr\Log\LoggerInterface;
use App\Enums\JobStatus;
use App\Infrastructure\Persistence\AI\AIJobMapper;
use App\Jobs\SendAIJobToQueue;
use App\Models\AIJob;
use DateTimeImmutable;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CreateAIJobHandler
{
    public function __construct(
        private readonly AIJobRepositoryInterface $jobs,
        private readonly LoggerInterface $logger
    ) {}

    public function handle(CreateAIJobCommand $command): AIJob
    {
        $type = AIJobType::tryFrom($command->type);
        if ($type === null) {
            throw ValidationException::withMessages(['type' => 'Unsupported AI job type.']);
        }

        $now = new DateTimeImmutable();
        $jobEntity = $this->jobs->create([
            'id' => (string) Str::uuid(),
            'user_id' => $command->userId,
            'workspace_id' => $command->workspaceId,
            'type' => $type,
            'payload' => $command->payload,
            'status' => JobStatus::PENDING,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->logger->info('AI job created', [
            'job_id' => $jobEntity->id,
            'job_type' => $type->value,
            'user_id' => $command->userId,
        ]);

        /** @var AIJob $model */
        $model = AIJobMapper::toModel($jobEntity);
        SendAIJobToQueue::dispatch($model)->onQueue('default');

        return $model;
    }
}
