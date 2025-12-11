<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\DefaultDataService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public const DEMO_EMAIL = 'demo@kanban.local';
    public const DEMO_PASSWORD = 'Demo123!';

    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => self::DEMO_EMAIL],
            [
                'name' => 'Demo Пользователь',
                'password' => Hash::make(self::DEMO_PASSWORD),
                'phone' => '+7 (999) 123-45-67',
                'telegram_username' => 'kanban_demo',
            ]
        );
        
        $defaultDataService = app(DefaultDataService::class);
        $defaultDataService->createDefaultWorkspaceForUser($user);
    }
}
