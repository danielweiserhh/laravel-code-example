<?php

declare(strict_types=1);

namespace App\Application\AI\UseCases;

use App\Domain\Inbox\Repositories\InboxItemRepositoryInterface;
use App\Enums\AIJobType;
use App\Models\AIJob;

final class ParseInboxItemHandler
{
    public function __construct(
        private readonly InboxItemRepositoryInterface $inboxItemRepository,
        private readonly CreateAIJobHandler $createAIJob
    ) {}

    public function handle(ParseInboxItemCommand $command): AIJob
    {
        $item = $this->inboxItemRepository->findOrFail($command->inboxItemId);

        return $this->createAIJob->handle(
            new CreateAIJobCommand(
                userId: $item->userId,
                workspaceId: $item->workspaceId,
                type: AIJobType::PARSE_INBOX_ITEM->value,
                payload: [
                    'inbox_item_id' => $item->id,
                    'content' => $item->content,
                    'language' => $command->language,
                ]
            )
        );
    }
}
