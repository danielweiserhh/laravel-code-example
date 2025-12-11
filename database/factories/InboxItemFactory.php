<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Card;
use App\Models\InboxItem;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

class InboxItemFactory extends Factory
{
    protected $model = InboxItem::class;
    
    public function definition(): array
    {
        $taskPrefixes = [
            'Купить',
            'Позвонить',
            'Написать',
            'Проверить',
            'Подготовить',
            'Сделать',
            'Отправить',
            'Обсудить',
        ];

        $taskSuffixes = [
            'продукты на неделю',
            'в банк по поводу карты',
            'отчёт за месяц',
            'письмо партнёрам',
            'презентацию для встречи',
            'задачи на завтра',
            'документы для бухгалтерии',
            'план на следующую неделю',
        ];

        return [
            'user_id' => User::factory(),
            'workspace_id' => Workspace::factory(),
            'content' => fake()->randomElement($taskPrefixes).' '.fake()->randomElement($taskSuffixes),
            'source' => 'manual',
            'is_processed' => false,
            'converted_to_card_id' => null,
            'ai_suggestions' => null,
        ];
    }
    
    public function processed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_processed' => true,
        ]);
    }
    
    public function convertedToCard(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_processed' => true,
            'converted_to_card_id' => Card::factory(),
        ]);
    }
    
    public function fromVoice(): static
    {
        return $this->state(fn (array $attributes): array => [
            'source' => 'voice',
        ]);
    }
    
    public function withAiSuggestions(): static
    {
        return $this->state(fn (array $attributes): array => [
            'ai_suggestions' => [
                'suggested_title' => fake()->sentence(4),
                'suggested_board' => 'Моя доска',
                'suggested_list' => 'Сегодня',
                'confidence' => fake()->randomFloat(2, 0.7, 1.0),
            ],
        ]);
    }
}
