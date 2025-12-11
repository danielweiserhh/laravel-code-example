<?php

declare(strict_types=1);

namespace App\Application\AI\UseCases;

use App\Domain\AI\Repositories\AIJobRepositoryInterface;
use App\Domain\Boards\Repositories\CardRepositoryInterface;
use App\Enums\AIJobType;
use Psr\Log\LoggerInterface;
use App\Enums\JobStatus;
use App\Jobs\SendAIJobToQueue;
use App\Models\AIJob;
use App\Models\Card;
use Illuminate\Support\Str;

final class SuggestCardRewriteHandler
{
    public function __construct(
        private readonly AIJobRepositoryInterface $jobs,
        private readonly CardRepositoryInterface $cardRepository,
        private readonly LoggerInterface $logger
    ) {}

    public function handle(SuggestCardRewriteCommand $command): AIJob
    {
        $domainCard = $this->cardRepository->findOrFail($command->cardId);

        $card = Card::findOrFail($command->cardId);
        $card->loadMissing('board');
        $workspaceId = $card->board?->workspace_id;

        $job = $this->jobs->create([
            'id' => (string) Str::uuid(),
            'user_id' => $command->userId,
            'workspace_id' => $workspaceId,
            'type' => AIJobType::SUGGEST_CARD_REWRITE,
            'payload' => [
                'card_id' => $command->cardId,
                'language' => $command->language,
                'fields' => $command->fields,
                'current' => [
                    'title' => $domainCard->title,
                    'description' => $domainCard->description,
                ],
            ],
            'status' => JobStatus::PENDING,
        ]);

        $this->logger->info('AI job created for card rewrite', [
            'job_id' => $job->id,
            'card_id' => $command->cardId,
            'user_id' => $command->userId,
        ]);

        SendAIJobToQueue::dispatch($job)->onQueue('default');

        return $job;
    }
}
