<?php

declare(strict_types=1);

namespace App\Application\Boards\UseCases;

use App\Domain\Boards\Repositories\BoardRepositoryInterface;
use App\DTOs\BoardUpdateDTO;
use App\Models\Board;
use Illuminate\Support\Facades\DB;

final class UpdateBoardHandler
{
    public function __construct(
        private readonly BoardRepositoryInterface $boardRepository
    ) {}

    public function handle(UpdateBoardCommand $command): Board
    {
        return DB::transaction(function () use ($command) {
            
            $dto = new BoardUpdateDTO(
                name: $command->name,
                description: $command->description,
                color: $command->color,
                privacy: $command->privacy,
                isFavorite: $command->isFavorite,
                settings: $command->settings
            );

            
            $domainBoard = $this->boardRepository->update($command->boardId, $dto);

            
            return Board::findOrFail($domainBoard->id);
        });
    }
}
