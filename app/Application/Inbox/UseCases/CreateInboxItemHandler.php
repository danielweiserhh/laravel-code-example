<?php

declare(strict_types=1);

namespace App\Application\Inbox\UseCases;

use App\Domain\Inbox\Repositories\InboxItemRepositoryInterface;
use App\Domain\Inbox\Services\InboxDomainService;
use App\Domain\Inbox\ValueObjects\InboxItem as DomainInboxItem;
use App\Domain\Shared\ValueObjects\UserId;
use Illuminate\Support\Facades\DB;

final class CreateInboxItemHandler
{
    public function __construct(
        private readonly InboxItemRepositoryInterface $inbox,
        private readonly InboxDomainService $inboxDomainService
    ) {}

    public function handle(CreateInboxItemCommand $command): DomainInboxItem
    {
        return DB::transaction(function () use ($command) {
            
            $this->inboxDomainService->validateContent($command->content);

            $userId = new UserId($command->userId);

            return $this->inbox->create([
                'content' => $command->content,
                'workspace_id' => $command->workspaceId,
                'source' => $command->source,
            ], $userId);
        });
    }
}
