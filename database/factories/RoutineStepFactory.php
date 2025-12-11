<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Routine;
use App\Models\RoutineStep;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoutineStepFactory extends Factory
{
    protected $model = RoutineStep::class;
    
    public function definition(): array
    {
        $stepTitles = [
            'Проснуться и встать',
            'Выпить стакан воды',
            'Сделать зарядку',
            'Принять душ',
            'Позавтракать',
            'Проверить план на день',
            'Медитация',
            'Чтение',
            'Прогулка',
        ];

        return [
            'routine_id' => Routine::factory(),
            'title' => fake()->randomElement($stepTitles),
            'description' => fake()->optional()->sentence(),
            'position' => 0,
            'duration_minutes' => fake()->randomElement([5, 10, 15, 20, 30]),
        ];
    }
    
    public function position(int $position): static
    {
        return $this->state(fn (array $attributes): array => [
            'position' => $position,
        ]);
    }
    
    public function withoutDuration(): static
    {
        return $this->state(fn (array $attributes): array => [
            'duration_minutes' => null,
        ]);
    }
    
    public function short(): static
    {
        return $this->state(fn (array $attributes): array => [
            'duration_minutes' => 5,
        ]);
    }
    
    public function long(): static
    {
        return $this->state(fn (array $attributes): array => [
            'duration_minutes' => fake()->numberBetween(30, 60),
        ]);
    }
}
