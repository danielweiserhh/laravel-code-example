<?php

namespace Database\Seeders;

use App\Enums\EnergyLevel;
use App\Enums\TaskType;
use App\Models\Board;
use App\Models\Card;
use App\Models\ListModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CardSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', \Database\Seeders\UserSeeder::DEMO_EMAIL)->first();

        if (! $user) {
            return;
        }
        
        $board = Board::whereHas('workspace.members', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->where('name', 'Моя доска')
            ->first();

        if (! $board) {
            return;
        }
        
        $todayList = ListModel::where('board_id', $board->id)
            ->where('name', 'Сегодня')
            ->first();
        $thisWeekList = ListModel::where('board_id', $board->id)
            ->where('name', 'На этой неделе')
            ->first();
        $laterList = ListModel::where('board_id', $board->id)
            ->where('name', 'Потом')
            ->first();
        
        if ($todayList) {
            $todayCards = [
                [
                    'title' => 'Завершить отчет по проекту',
                    'description' => 'Подготовить финальную версию отчета и отправить руководителю',
                    'due_date' => Carbon::today(),
                    'energy_level' => EnergyLevel::HIGH,
                    'task_type' => TaskType::TASK,
                ],
                [
                    'title' => 'Позвонить клиенту',
                    'description' => 'Обсудить детали нового контракта',
                    'due_date' => Carbon::today(),
                    'energy_level' => EnergyLevel::MEDIUM,
                    'task_type' => TaskType::TASK,
                ],
                [
                    'title' => 'Проверить почту',
                    'description' => 'Ответить на срочные письма',
                    'due_date' => Carbon::today(),
                    'energy_level' => EnergyLevel::LOW,
                    'task_type' => TaskType::TASK,
                ],
            ];

            foreach ($todayCards as $index => $cardData) {
                Card::updateOrCreate(
                    [
                        'board_id' => $board->id,
                        'list_id' => $todayList->id,
                        'title' => $cardData['title'],
                    ],
                    [
                        'description' => $cardData['description'],
                        'position' => $index + 1,
                        'due_date' => $cardData['due_date'],
                        'energy_level' => $cardData['energy_level'],
                        'task_type' => $cardData['task_type'],
                        'is_completed' => false,
                    ]
                );
            }
        }
        
        if ($thisWeekList) {
            $thisWeekCards = [
                [
                    'title' => 'Подготовить презентацию',
                    'description' => 'Создать слайды для встречи с командой',
                    'due_date' => Carbon::now()->addDays(3),
                    'energy_level' => EnergyLevel::MEDIUM,
                    'task_type' => TaskType::TASK,
                ],
                [
                    'title' => 'Встреча с дизайнером',
                    'description' => 'Обсудить макеты нового интерфейса',
                    'due_date' => Carbon::now()->addDays(5),
                    'energy_level' => EnergyLevel::HIGH,
                    'task_type' => TaskType::TASK,
                ],
                [
                    'title' => 'Обновить документацию',
                    'description' => 'Дополнить раздел API новыми методами',
                    'due_date' => Carbon::now()->addDays(6),
                    'energy_level' => EnergyLevel::LOW,
                    'task_type' => TaskType::TASK,
                ],
            ];

            foreach ($thisWeekCards as $index => $cardData) {
                Card::updateOrCreate(
                    [
                        'board_id' => $board->id,
                        'list_id' => $thisWeekList->id,
                        'title' => $cardData['title'],
                    ],
                    [
                        'description' => $cardData['description'],
                        'position' => $index + 1,
                        'due_date' => $cardData['due_date'],
                        'energy_level' => $cardData['energy_level'],
                        'task_type' => $cardData['task_type'],
                        'is_completed' => false,
                    ]
                );
            }
        }
        
        if ($laterList) {
            $laterCards = [
                [
                    'title' => 'Изучить новый фреймворк',
                    'description' => 'Посмотреть туториалы и создать тестовый проект',
                    'due_date' => null,
                    'energy_level' => EnergyLevel::LOW,
                    'task_type' => TaskType::TASK,
                ],
                [
                    'title' => 'Оптимизировать базу данных',
                    'description' => 'Провести аудит производительности и добавить индексы',
                    'due_date' => null,
                    'energy_level' => EnergyLevel::MEDIUM,
                    'task_type' => TaskType::TASK,
                ],
                [
                    'title' => 'Рефакторинг старого кода',
                    'description' => 'Улучшить структуру и читаемость кода',
                    'due_date' => null,
                    'energy_level' => EnergyLevel::LOW,
                    'task_type' => TaskType::TASK,
                ],
            ];

            foreach ($laterCards as $index => $cardData) {
                Card::updateOrCreate(
                    [
                        'board_id' => $board->id,
                        'list_id' => $laterList->id,
                        'title' => $cardData['title'],
                    ],
                    [
                        'description' => $cardData['description'],
                        'position' => $index + 1,
                        'due_date' => $cardData['due_date'],
                        'energy_level' => $cardData['energy_level'],
                        'task_type' => $cardData['task_type'],
                        'is_completed' => false,
                    ]
                );
            }
        }
    }
}
