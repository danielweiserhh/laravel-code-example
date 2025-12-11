<?php

declare(strict_types=1);

namespace App\Application\Users\UseCases;

use App\Domain\Users\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class UpdateProfileHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    
    public function handle(UpdateProfileCommand $command): User
    {
        return DB::transaction(function () use ($command) {
            $user = $this->userRepository->findOrFail($command->userId);

            if ($command->name !== null) {
                $user->name = $command->name;
            }
            if ($command->phone !== null) {
                $user->phone = $command->phone;
            }
            if ($command->telegramUsername !== null) {
                
                $user->telegram_username = ltrim($command->telegramUsername, '@');
            }
            if ($command->aiModel !== null) {
                $user->ai_model = $command->aiModel;
            }
            if ($command->speechModel !== null) {
                $user->speech_model = $command->speechModel;
            }

            $this->userRepository->save($user);

            return $user->fresh();
        });
    }
}
