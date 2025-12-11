<?php

namespace Database\Factories;

use App\Enums\BoardPrivacy;
use App\Models\Board;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;


class BoardFactory extends Factory
{
    protected $model = Board::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'color' => fake()->hexColor(),
            'privacy' => BoardPrivacy::PRIVATE,
            'is_favorite' => false,
            'settings' => [],
        ];
    }
}
