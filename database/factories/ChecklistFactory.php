<?php

namespace Database\Factories;

use App\Models\Card;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChecklistFactory extends Factory
{
    public function definition(): array
    {
        return [
            'card_id' => Card::factory(),
            'title' => $this->faker->sentence(3),
            'position' => $this->faker->numberBetween(0, 10),
        ];
    }
}
