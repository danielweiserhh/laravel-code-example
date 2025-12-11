<?php

namespace Database\Factories;

use App\Models\Board;
use App\Models\Card;
use App\Models\ListModel;
use Illuminate\Database\Eloquent\Factories\Factory;


class CardFactory extends Factory
{
    protected $model = Card::class;

    public function definition(): array
    {
        return [
            'list_id' => ListModel::factory(),
            'board_id' => Board::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'position' => fake()->numberBetween(0, 10),
            'start_date' => null,
            'due_date' => null,
            'energy_level' => null,
            'task_type' => null,
            'is_completed' => false,
            'completed_at' => null,
        ];
    }
}
