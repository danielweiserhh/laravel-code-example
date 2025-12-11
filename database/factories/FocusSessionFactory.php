<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\FocusSessionStatus;
use App\Models\Card;
use App\Models\FocusSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;


class FocusSessionFactory extends Factory
{
    protected $model = FocusSession::class;
    
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'card_id' => null,
            'duration_minutes' => fake()->randomElement([15, 25, 30, 45, 60]),
            'status' => FocusSessionStatus::PENDING,
            'is_group' => false,
            'video_link' => null,
            'started_at' => null,
            'ended_at' => null,
        ];
    }
    
    public function withCard(): static
    {
        return $this->state(fn (array $attributes): array => [
            'card_id' => Card::factory(),
        ]);
    }
    
    public function group(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_group' => true,
            'video_link' => fake()->url(),
        ]);
    }
    
    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => FocusSessionStatus::ACTIVE,
            'started_at' => now(),
        ]);
    }
    
    public function paused(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => FocusSessionStatus::PAUSED,
            'started_at' => now()->subMinutes(10),
        ]);
    }
    
    public function completed(): static
    {
        $startedAt = now()->subMinutes(fake()->numberBetween(15, 60));

        return $this->state(fn (array $attributes): array => [
            'status' => FocusSessionStatus::COMPLETED,
            'started_at' => $startedAt,
            'ended_at' => $startedAt->addMinutes($attributes['duration_minutes'] ?? 25),
        ]);
    }
    
    public function pomodoro(): static
    {
        return $this->state(fn (array $attributes): array => [
            'duration_minutes' => 25,
        ]);
    }
}
