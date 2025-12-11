<?php

declare(strict_types=1);

namespace App\Application\AI\UseCases;

use App\Domain\AI\Entities\AIJobEntity;
use App\Domain\AI\Repositories\AIJobRepositoryInterface;
use App\Domain\Inbox\Repositories\InboxItemRepositoryInterface;
use Psr\Log\LoggerInterface;
use App\Enums\AIJobType;
use App\Enums\JobStatus;
use App\Events\AIJobCompleted;
use App\Infrastructure\Persistence\AI\AIJobMapper;
use App\Models\AIJob;
use Illuminate\Support\Facades\DB;

final class ProcessAIJobWebhookHandler
{
    public function __construct(
        private readonly AIJobRepositoryInterface $aiJobRepository,
        private readonly InboxItemRepositoryInterface $inboxItemRepository,
        private readonly LoggerInterface $logger
    ) {}

    public function handle(ProcessAIJobWebhookCommand $command): void
    {
        DB::transaction(function () use ($command) {
            $aiJobEntity = $this->aiJobRepository->find($command->jobId);

            if (! $aiJobEntity) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException('AI job not found');
            }

            if ($command->status === 'failed') {
                $updatedEntity = $aiJobEntity->withStatus(
                    JobStatus::FAILED->value,
                    null,
                    $command->errorMessage
                );
                $savedEntity = $this->aiJobRepository->save($updatedEntity);

                $this->logger->error('AI job finished with error', [
                    'job_id' => $savedEntity->id,
                    'job_type' => $command->jobType,
                    'user_id' => $savedEntity->userId,
                    'error_message' => $command->errorMessage,
                ]);

                /** @var AIJob $model */
                $model = AIJobMapper::toModel($savedEntity);
                event(new AIJobCompleted($model->fresh()));

                return;
            }

            $updatedEntity = $aiJobEntity->withStatus(JobStatus::SUCCEEDED->value, $command->result);
            $savedEntity = $this->aiJobRepository->save($updatedEntity);

            $this->handleJobType($savedEntity, $command->jobType, $command->result);

            /** @var AIJob $model */
            $model = AIJobMapper::toModel($savedEntity);
            event(new AIJobCompleted($model->fresh()));
        });
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function handleJobType(AIJobEntity $aiJob, string $jobType, array $result): void
    {
        if ($jobType === AIJobType::PARSE_INBOX_ITEM->value) {
            try {
                $this->handleParseInboxItem($aiJob, $result);
            } catch (\Throwable $e) {
                $this->logger->error('Error processing AI job type', [
                    'job_id' => $aiJob->id,
                    'job_type' => $jobType,
                    'user_id' => $aiJob->userId,
                    'error' => $e->getMessage(),
                ]);
                throw new \DomainException('Error processing AI job result: '.$e->getMessage(), 0, $e);
            }

            return;
        }
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function handleParseInboxItem(AIJobEntity $aiJob, array $result): void
    {
        $inboxItemId = $aiJob->payload['inbox_item_id'] ?? null;

        if (! $inboxItemId) {
            return;
        }

        $inboxItem = $this->inboxItemRepository->find((int) $inboxItemId);

        if (! $inboxItem) {
            return;
        }

        $this->inboxItemRepository->updateAISuggestions($inboxItem->id, [
            'title' => $result['title'] ?? null,
            'due_date' => $result['due_date'] ?? null,
            'duration_estimate' => $result['duration_estimate'] ?? null,
            'suggested_board' => $result['suggested_board'] ?? null,
            'suggested_list' => $result['suggested_list'] ?? null,
            'suggested_assignee' => $result['suggested_assignee'] ?? null,
        ]);
    }
}
