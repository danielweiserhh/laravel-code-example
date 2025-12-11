<?php

declare(strict_types=1);

namespace App\Application\Inbox\UseCases;

use App\Domain\Inbox\Repositories\InboxItemRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class DeleteInboxItemHandler
{
    public function __construct(
        private readonly InboxItemRepositoryInterface $inboxRepository
    ) {}

    public function handle(DeleteInboxItemCommand $command): void
    {
        DB::transaction(function () use ($command) {
            $this->inboxRepository->delete($command->inboxItemId);
        });
    }
}
