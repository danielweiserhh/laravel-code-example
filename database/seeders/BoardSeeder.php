<?php

namespace Database\Seeders;

use App\Enums\BoardPrivacy;
use App\Models\Board;
use App\Models\Label;
use App\Models\ListModel;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;

class BoardSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', \Database\Seeders\UserSeeder::DEMO_EMAIL)->first();
        $workspace = Workspace::where('slug', 'demo-workspace')->first();

        if (! $user || ! $workspace) {
            return;
        }

        $boards = [
            [
                'name' => 'Product Roadmap',
                'description' => 'Основные инициативы команды и статус их выполнения.',
                'color' => '#2563eb',
                'privacy' => BoardPrivacy::WORKSPACE,
                'position' => 1,
                'lists' => [
                    ['name' => 'Идеи', 'position' => 1],
                    ['name' => 'В работе', 'position' => 2],
                    ['name' => 'Готово', 'position' => 3],
                ],
            ],
            [
                'name' => 'Личное развитие',
                'description' => 'Трекер привычек и задач для личного роста.',
                'color' => '#14b8a6',
                'privacy' => BoardPrivacy::PRIVATE,
                'position' => 2,
                'lists' => [
                    ['name' => 'Навыки', 'position' => 1],
                    ['name' => 'Чтение', 'position' => 2],
                    ['name' => 'Закреплено', 'position' => 3],
                ],
            ],
        ];

        foreach ($boards as $boardData) {
            $board = Board::updateOrCreate(
                [
                    'workspace_id' => $workspace->id,
                    'name' => $boardData['name'],
                ],
                [
                    'description' => $boardData['description'],
                    'color' => $boardData['color'],
                    'privacy' => $boardData['privacy'],
                    'is_favorite' => true,
                    'position' => $boardData['position'],
                    'settings' => ['grouping' => 'status'],
                ]
            );

            $board->members()->syncWithoutDetaching([
                $user->id => ['role' => 'admin'],
            ]);

            collect($boardData['lists'])->each(function (array $listData) use ($board) {
                ListModel::updateOrCreate(
                    [
                        'board_id' => $board->id,
                        'name' => $listData['name'],
                    ],
                    [
                        'position' => $listData['position'],
                    ]
                );
            });

            $labels = [
                ['name' => '🔥 В приоритете', 'color' => '#ef4444'],
                ['name' => '🧪 Эксперимент', 'color' => '#a855f7'],
                ['name' => '✅ Быстрая победа', 'color' => '#22c55e'],
            ];

            collect($labels)->each(fn (array $label) => Label::updateOrCreate(
                [
                    'board_id' => $board->id,
                    'name' => $label['name'],
                ],
                ['color' => $label['color']]
            ));
        }
    }
}
