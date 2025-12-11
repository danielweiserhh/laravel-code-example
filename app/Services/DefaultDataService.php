<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BoardPrivacy;
use App\Models\Board;
use App\Models\ListModel;
use App\Models\Routine;
use App\Models\RoutineStep;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Str;

class DefaultDataService
{
    public function createDefaultWorkspaceForUser(User $user): Workspace
    {
        
        $existingWorkspace = $user->workspaces()->first();
        if ($existingWorkspace) {
            return $existingWorkspace;
        }

        $workspace = Workspace::create([
            'name' => 'Мое рабочее пространство',
            'description' => 'Ваше личное рабочее пространство',
            'slug' => 'my-workspace-'.Str::random(8),
            'settings' => [
                'timezone' => 'Europe/Moscow',
                'working_hours' => ['start' => '09:00', 'end' => '18:00'],
            ],
        ]);

        $workspace->members()->attach($user->id, ['role' => 'owner']);
        
        $board = Board::create([
            'workspace_id' => $workspace->id,
            'name' => 'Моя доска',
            'description' => 'Основная доска для задач',
            'color' => '#2563eb',
            'privacy' => BoardPrivacy::PRIVATE,
            'is_favorite' => true,
            'position' => 1,
            'settings' => ['grouping' => 'status'],
        ]);

        $board->members()->attach($user->id, ['role' => 'admin']);
        
        $lists = [
            ['name' => 'Сегодня', 'position' => 1],
            ['name' => 'На этой неделе', 'position' => 2],
            ['name' => 'Потом', 'position' => 3],
        ];

        foreach ($lists as $listData) {
            ListModel::create([
                'board_id' => $board->id,
                'name' => $listData['name'],
                'position' => $listData['position'],
            ]);
        }
        
        $this->createDefaultPeriodicTasks($user, $workspace);

        return $workspace;
    }

    private function createDefaultPeriodicTasks(User $user, Workspace $workspace): void
    { 
        $periodicTasks = [
            [
                'name' => 'Утро',
                'description' => 'Утренние периодические задачи',
                'type' => 'morning',
                'steps' => [],
            ],
            [
                'name' => 'День',
                'description' => 'Дневные периодические задачи',
                'type' => 'work',
                'steps' => [],
            ],
            [
                'name' => 'Вечер',
                'description' => 'Вечерние периодические задачи',
                'type' => 'evening',
                'steps' => [],
            ],
        ];

        foreach ($periodicTasks as $taskData) {
            $routine = Routine::create([
                'workspace_id' => $workspace->id,
                'user_id' => $user->id,
                'name' => $taskData['name'],
                'description' => $taskData['description'],
                'type' => $taskData['type'],
                'is_active' => true,
                'settings' => [],
            ]);
            
            $steps = $taskData['steps'];
            
            if (is_array($steps) && count($steps) > 0) {
                foreach ($steps as $index => $step) {
                    if (is_array($step)) {
                        RoutineStep::create([
                            'routine_id' => $routine->id,
                            'title' => $step['title'] ?? '',
                            'description' => $step['description'] ?? null,
                            'position' => $index + 1,
                            'duration_minutes' => $step['duration_minutes'] ?? null,
                        ]);
                    }
                }
            }
        }
    }
}
