<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;

class WorkspaceSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', \Database\Seeders\UserSeeder::DEMO_EMAIL)->first();

        if (! $user) {
            return;
        }

        $workspace = Workspace::updateOrCreate(
            ['slug' => 'demo-workspace'],
            [
                'name' => 'Demo Workspace',
                'description' => 'Рабочее пространство с преднастроенными досками, рутинами и заметками.',
                'settings' => [
                    'timezone' => 'Europe/Moscow',
                    'working_hours' => ['start' => '09:00', 'end' => '18:00'],
                ],
            ]
        );

        $workspace->members()->syncWithoutDetaching([
            $user->id => ['role' => 'owner'],
        ]);
    }
}
