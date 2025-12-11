<?php

declare(strict_types=1);

namespace App\Application\Today\UseCases;

use App\Application\Boards\UseCases\CreateCardCommand;
use App\Application\Boards\UseCases\CreateCardHandler;
use App\Domain\Boards\Repositories\BoardRepositoryInterface;
use App\Domain\Boards\Repositories\ListRepositoryInterface;
use App\Domain\Inbox\Repositories\InboxItemRepositoryInterface;
use App\Domain\Users\Repositories\UserRepositoryInterface;
use DomainException;

final class AddInboxItemsToTodayHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly BoardRepositoryInterface $boardRepository,
        private readonly ListRepositoryInterface $listRepository,
        private readonly InboxItemRepositoryInterface $inboxItemRepository,
        private readonly CreateCardHandler $createCardHandler
    ) {}

    
    public function handle(AddInboxItemsToTodayCommand $command): array
    {
        $user = $this->userRepository->findOrFail($command->userId);

        $todayList = $this->getTodayList($user);
        if (! $todayList) {
            throw new DomainException('Колонка "Сегодня" не найдена');
        }

        $userId = new \App\Domain\Shared\ValueObjects\UserId($command->userId);
        $inboxItems = [];
        foreach ($command->inboxItemIds as $itemId) {
            $item = $this->inboxItemRepository->find($itemId);
            if ($item && $item->userId === $userId->value && ! $item->isProcessed) {
                $inboxItems[] = $item;
            }
        }

        $createdCards = [];
        foreach ($inboxItems as $inboxItem) {
            $createCommand = new CreateCardCommand(
                listId: $todayList->id,
                title: $inboxItem->content,
                description: null,
                position: null,
                assigneeId: null,
                checklist: null,
                startDate: null,
                dueDate: null,
                energyLevel: null,
                taskType: null,
                customFields: null
            );
            $card = $this->createCardHandler->handle($createCommand);

            
            $this->inboxItemRepository->update($inboxItem->id, [
                'content' => null,
                'is_processed' => true,
            ]);
            $this->inboxItemRepository->updateConvertedToCardId($inboxItem->id, $card->id);

            $createdCards[] = [
                'id' => $card->id,
                'title' => $card->title,
                'board' => $card->board?->name ?? 'Без доски',
            ];
        }

        return $createdCards;
    }

    private function getTodayList(\App\Models\User $user): ?\App\Models\ListModel
    {
        
        $domainBoard = $this->boardRepository->findByNameInUserWorkspaces($user->id, 'Моя доска');

        if (! $domainBoard) {
            return null;
        }

        
        $domainLists = $this->listRepository->getByBoard($domainBoard->id);
        foreach ($domainLists as $domainList) {
            if ($domainList->name === 'Сегодня' && ! $domainList->isArchived) {
                return \App\Models\ListModel::findOrFail($domainList->id);
            }
        }

        return null;
    }
}
