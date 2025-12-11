<?php

namespace Database\Seeders;

use App\Models\Routine;
use App\Models\RoutineStep;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoutineSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', \Database\Seeders\UserSeeder::DEMO_EMAIL)->first();

        if (! $user) {
            return;
        }

        $workspace = $user->workspaces()->first();

        if (! $workspace) {
            return;
        }
        
        $morningRoutine = Routine::where('workspace_id', $workspace->id)
            ->where('user_id', $user->id)
            ->where('name', 'Утро')
            ->first();

        $dayRoutine = Routine::where('workspace_id', $workspace->id)
            ->where('user_id', $user->id)
            ->where('name', 'День')
            ->first();

        $eveningRoutine = Routine::where('workspace_id', $workspace->id)
            ->where('user_id', $user->id)
            ->where('name', 'Вечер')
            ->first();
        
        if ($morningRoutine) {
            $morningSteps = [
                ['title' => 'Проверить календарь', 'description' => 'Посмотреть запланированные встречи на день', 'duration_minutes' => 5],
                ['title' => 'Запланировать задачи на сегодня', 'description' => 'Выбрать 3 главные задачи на день', 'duration_minutes' => 10],
                ['title' => 'Сделать зарядку', 'description' => 'Короткая разминка для бодрости', 'duration_minutes' => 15],
            ];

            foreach ($morningSteps as $index => $step) {
                RoutineStep::updateOrCreate(
                    [
                        'routine_id' => $morningRoutine->id,
                        'title' => $step['title'],
                    ],
                    [
                        'position' => $index + 1,
                        'description' => $step['description'],
                        'duration_minutes' => $step['duration_minutes'],
                    ]
                );
            }
        }
        
        if ($dayRoutine) {
            $daySteps = [
                ['title' => 'Проверить почту', 'description' => 'Ответить на важные письма', 'duration_minutes' => 15],
                ['title' => 'Сделать перерыв', 'description' => 'Отдохнуть и перекусить', 'duration_minutes' => 20],
                ['title' => 'Обновить статус задач', 'description' => 'Отметить выполненные задачи', 'duration_minutes' => 10],
            ];

            foreach ($daySteps as $index => $step) {
                RoutineStep::updateOrCreate(
                    [
                        'routine_id' => $dayRoutine->id,
                        'title' => $step['title'],
                    ],
                    [
                        'position' => $index + 1,
                        'description' => $step['description'],
                        'duration_minutes' => $step['duration_minutes'],
                    ]
                );
            }
        }
        
        if ($eveningRoutine) {
            $eveningSteps = [
                ['title' => 'Подвести итоги дня', 'description' => 'Записать что было сделано', 'duration_minutes' => 10],
                ['title' => 'Подготовить план на завтра', 'description' => 'Выбрать задачи на следующий день', 'duration_minutes' => 15],
                ['title' => 'Расслабиться', 'description' => 'Заняться хобби или отдохнуть', 'duration_minutes' => 30],
            ];

            foreach ($eveningSteps as $index => $step) {
                RoutineStep::updateOrCreate(
                    [
                        'routine_id' => $eveningRoutine->id,
                        'title' => $step['title'],
                    ],
                    [
                        'position' => $index + 1,
                        'description' => $step['description'],
                        'duration_minutes' => $step['duration_minutes'],
                    ]
                );
            }
        }
    }
}
