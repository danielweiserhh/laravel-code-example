<?php

declare(strict_types=1);

namespace App\Application\Boards\UseCases;

use App\Domain\Boards\Repositories\CardRepositoryInterface;
use App\Domain\Boards\Repositories\ListRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class DeleteListHandler
{
    public function __construct(
        private readonly ListRepositoryInterface $listRepository,
        private readonly CardRepositoryInterface $cardRepository
    ) {}

    public function handle(DeleteListCommand $command): void
    {
        DB::transaction(function () use ($command) {
            $domainList = $this->listRepository->findOrFail($command->listId);

            $cards = $this->cardRepository->getByList($domainList->id);
            $hasActiveCards = false;
            foreach ($cards as $card) {
                if (! $card->isCompleted) {
                    $hasActiveCards = true;
                    break;
                }
            }

            if ($hasActiveCards) {
                throw new \DomainException('Невозможно удалить колонку с активными задачами');
            }

            $this->listRepository->delete($command->listId);
        });
    }
}
