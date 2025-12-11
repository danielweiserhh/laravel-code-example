<?php

declare(strict_types=1);

namespace App\Application\Inbox\UseCases;

use App\Domain\Inbox\Repositories\InboxItemRepositoryInterface;
use App\Domain\Inbox\Services\InboxDomainService;
use App\Domain\Inbox\ValueObjects\InboxItem as DomainInboxItem;
use Illuminate\Support\Facades\DB;

final class UpdateInboxItemHandler
{
    public function __construct(
        private readonly InboxDomainService $inboxDomainService,
        private readonly InboxItemRepositoryInterface $inboxRepository
    ) {}

    public function handle(UpdateInboxItemCommand $command): DomainInboxItem
    {
        return DB::transaction(function () use ($command) {
            
            if ($command->content !== null) {
                $this->inboxDomainService->validateContent($command->content);
            }

            return $this->inboxRepository->update($command->inboxItemId, [
                'content' => $command->content,
                'is_processed' => $command->isProcessed,
            ]);
        });
    }
}
