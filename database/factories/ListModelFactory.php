<?php

namespace Database\Factories;

use App\Models\Board;
use App\Models\ListModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ListModelFactory extends Factory
{
    protected $model = ListModel::class;

    public function definition(): array
    {
        return [
            'board_id' => Board::factory(),
            'name' => fake()->words(3, true),
            'position' => fake()->numberBetween(0, 10),
            'is_archived' => false,
        ];
    }
}
