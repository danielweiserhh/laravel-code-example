<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Routine;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoutineFactory extends Factory
{
    protected $model = Routine::class;
    
    public function definition(): array
    {
        $types = ['morning', 'evening', 'work', 'custom'];

        return [
            'workspace_id' => Workspace::factory(),
            'user_id' => User::factory(),
            'name' => fake()->randomElement([
                'Утренняя зарядка',
                'Вечерняя медитация',
                'Рабочий ритуал',
                'Еженедельный обзор',
                'Ежедневная уборка',
            ]),
            'description' => fake()->optional()->sentence(),
            'type' => fake()->randomElement($types),
            'is_active' => true,
            'settings' => [
                'days' => ['mon', 'tue', 'wed', 'thu', 'fri'],
                'time' => '09:00',
            ],
        ];
    }
    
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
    
    public function morning(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'morning',
            'name' => 'Утренняя рутина',
            'settings' => [
                'days' => ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'],
                'time' => '07:00',
            ],
        ]);
    }
    
    public function evening(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'evening',
            'name' => 'Вечерняя рутина',
            'settings' => [
                'days' => ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'],
                'time' => '21:00',
            ],
        ]);
    }
    
    public function work(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'work',
            'name' => 'Рабочий ритуал',
            'settings' => [
                'days' => ['mon', 'tue', 'wed', 'thu', 'fri'],
                'time' => '09:00',
            ],
        ]);
    }
}
