<?php

declare(strict_types=1);

namespace App\Application\Boards\UseCases;

use App\Domain\Boards\Repositories\BoardRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class DeleteBoardHandler
{
    public function __construct(
        private readonly BoardRepositoryInterface $boardRepository
    ) {}

    public function handle(DeleteBoardCommand $command): void
    {
        DB::transaction(function () use ($command) {
            $this->boardRepository->delete($command->boardId);
        });
    }
}
