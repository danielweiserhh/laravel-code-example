<?php

declare(strict_types=1);

namespace App\Application\Boards\UseCases;

use App\Domain\Boards\Repositories\CardRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class DeleteCardHandler
{
    public function __construct(
        private readonly CardRepositoryInterface $cardRepository
    ) {}

    public function handle(DeleteCardCommand $command): void
    {
        DB::transaction(function () use ($command) {
            
            $this->cardRepository->delete($command->cardId);
        });
    }
}
