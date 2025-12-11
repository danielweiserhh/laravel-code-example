<?php

declare(strict_types=1);

namespace App\Application\Today\UseCases;

final class AddInboxItemsToTodayCommand
{
    
    public function __construct(
        public readonly int $userId,
        public readonly array $inboxItemIds
    ) {}
}
