<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Boards;

use App\Domain\Boards\Repositories\CardRepositoryInterface;
use App\Domain\Boards\ValueObjects\Card as DomainCard;
use App\Models\Card;
use App\Models\ListModel;
use Carbon\Carbon;

final class EloquentCardRepository implements CardRepositoryInterface
{
    
    private function toDomain(Card $model): DomainCard
    {
        return DomainCard::fromArray([
            'id' => $model->id,
            'list_id' => $model->list_id,
            'board_id' => $model->board_id,
            'title' => $model->title,
            'description' => $model->description,
            'position' => $model->position ?? 0,
            'start_date' => $model->start_date?->toIso8601String(),
            'due_date' => $model->due_date?->toIso8601String(),
            'energy_level' => $model->energy_level?->value,
            'task_type' => $model->task_type?->value,
            'is_completed' => $model->is_completed ?? false,
            'completed_at' => $model->completed_at?->toIso8601String(),
            'cover_attachment_id' => $model->cover_attachment_id,
            'custom_fields' => $model->custom_fields,
            'created_at' => $model->created_at->toIso8601String(),
            'updated_at' => $model->updated_at->toIso8601String(),
        ]);
    }

    public function find(int $id): ?DomainCard
    {
        $model = Card::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findOrFail(int $id): DomainCard
    {
        $model = Card::findOrFail($id);

        return $this->toDomain($model);
    }

    public function findWithBoard(int $id): ?DomainCard
    {
        $model = Card::with('board')->find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findOrFailWithBoard(int $id): DomainCard
    {
        $model = Card::with('board')->findOrFail($id);

        return $this->toDomain($model);
    }
    
    public function create(array $payload): DomainCard
    {
        $card = new Card;
        $card->list_id = (int) $payload['list_id'];
        $card->board_id = isset($payload['board_id'])
            ? (int) $payload['board_id']
            : $this->resolveBoardId((int) $payload['list_id']);
        $card->title = (string) $payload['title'];
        $card->description = $payload['description'] ?? null;
        $card->position = isset($payload['position']) ? (int) $payload['position'] : 0;
        $card->start_date = $this->parseDate($payload['start_date'] ?? null);
        $card->due_date = $this->parseDate($payload['due_date'] ?? null);
        $card->energy_level = $this->resolveEnergyLevel($payload['energy_level'] ?? null);
        $card->task_type = $this->resolveTaskType($payload['task_type'] ?? null);

        if (array_key_exists('custom_fields', $payload)) {
            $card->custom_fields = $payload['custom_fields'];
        }

        if (array_key_exists('cover_attachment_id', $payload)) {
            $card->cover_attachment_id = $payload['cover_attachment_id'];
        }

        $card->save();

        if (array_key_exists('assignee_id', $payload) && $payload['assignee_id'] !== null) {
            $card->assignees()->sync([(int) $payload['assignee_id']]);
        } elseif (array_key_exists('assignees', $payload) && is_array($payload['assignees'])) {
            $card->assignees()->sync($payload['assignees']);
        }

        $this->syncChecklist($card, $payload['checklist'] ?? null);

        return $this->toDomain($card->load(['assignees', 'checklists.items'])->fresh());
    }
    
    public function update(int $id, array $payload): DomainCard
    {
        $card = Card::findOrFail($id);

        if (array_key_exists('list_id', $payload)) {
            $card->list_id = (int) $payload['list_id'];
        }

        if (array_key_exists('board_id', $payload)) {
            $card->board_id = (int) $payload['board_id'];
        }

        if (array_key_exists('title', $payload) && $payload['title'] !== null) {
            $card->title = (string) $payload['title'];
        }

        if (array_key_exists('description', $payload)) {
            $card->description = $payload['description'];
        }

        if (array_key_exists('position', $payload) && $payload['position'] !== null) {
            $card->position = (int) $payload['position'];
        }

        if (array_key_exists('start_date', $payload)) {
            $card->start_date = $this->parseDate($payload['start_date']);
        }

        if (array_key_exists('due_date', $payload)) {
            $card->due_date = $this->parseDate($payload['due_date']);
        }

        if (array_key_exists('energy_level', $payload)) {
            $card->energy_level = $this->resolveEnergyLevel($payload['energy_level']);
        }

        if (array_key_exists('task_type', $payload)) {
            $card->task_type = $this->resolveTaskType($payload['task_type']);
        }

        if (array_key_exists('is_completed', $payload)) {
            $card->is_completed = (bool) $payload['is_completed'];
            $card->completed_at = $card->is_completed ? now() : null;
        }

        if (array_key_exists('custom_fields', $payload)) {
            $card->custom_fields = $payload['custom_fields'];
        }

        if (array_key_exists('cover_attachment_id', $payload)) {
            $card->cover_attachment_id = $payload['cover_attachment_id'];
        }

        $card->save();

        if (array_key_exists('assignees', $payload) && is_array($payload['assignees'])) {
            $card->assignees()->sync($payload['assignees']);
        } elseif (array_key_exists('assignee_id', $payload) && $payload['assignee_id'] !== null) {
            $card->assignees()->sync([(int) $payload['assignee_id']]);
        }

        return $this->toDomain($card->fresh(['assignees', 'checklists']));
    }

    public function save(DomainCard $card): DomainCard
    {
        if ($card->id > 0) {    
            $model = Card::findOrFail($card->id);
            $model->list_id = $card->listId;
            $model->board_id = $card->boardId;
            $model->title = $card->title;
            $model->description = $card->description;
            $model->position = $card->position;
            $model->start_date = $card->startDate ? Carbon::parse($card->startDate) : null;
            $model->due_date = $card->dueDate ? Carbon::parse($card->dueDate) : null;
            $model->energy_level = $card->energyLevel ? \App\Enums\EnergyLevel::from($card->energyLevel) : null;
            $model->task_type = $card->taskType ? \App\Enums\TaskType::from($card->taskType) : null;
            $model->is_completed = $card->isCompleted;
            $model->completed_at = $card->completedAt ? Carbon::parse($card->completedAt) : null;
            $model->cover_attachment_id = $card->coverAttachmentId;
            $model->custom_fields = $card->customFields;
            $model->save();

            return $this->toDomain($model->fresh());
        }
        
        throw new \DomainException('Cannot save new card without DTO. Use create() method.');
    }

    public function delete(int $id): void
    {
        Card::findOrFail($id)->delete();
    }
    
    public function getByList(int $listId): array
    {
        $models = Card::with('board')
            ->where('list_id', $listId)
            ->orderBy('position')
            ->get();

        return $this->toDomainCollection($models);
    }
    
    public function getActiveCardsForUser(int $userId): array
    {
        $models = Card::whereHas('assignees', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->where('is_completed', false)
            ->where(function ($q) {
                $q->whereNull('due_date')
                    ->orWhere('due_date', '>=', now()->startOfDay());
            })
            ->with(['board'])
            ->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->toDomainCollection($models);
    }
    
    private function toDomainCollection($models): array
    {
        return $models->map(fn (Card $model) => $this->toDomain($model))->all();
    }

    public function getMaxPositionForList(int $listId): int
    {
        $max = Card::where('list_id', $listId)->max('position');

        return $max !== null ? (int) $max : 0;
    }

    public function getMaxPositionForListExcluding(int $listId, ?int $excludeCardId = null): int
    {
        $query = Card::where('list_id', $listId);

        if ($excludeCardId !== null) {
            $query->where('id', '!=', $excludeCardId);
        }

        $max = $query->max('position');

        return $max !== null ? (int) $max : 0;
    }

    public function updatePositions(array $positionMap): void
    {
        foreach ($positionMap as $cardId => $position) {
            Card::where('id', $cardId)->update(['position' => $position]);
        }
    }

    private function parseDate(?string $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value);
    }

    private function resolveEnergyLevel(null|string|int $value): ?\App\Enums\EnergyLevel
    {
        if ($value === null || $value === '') {
            return null;
        }

        $stringValue = (string) $value;

        return \App\Enums\EnergyLevel::tryFrom($stringValue);
    }

    private function resolveTaskType(?string $value): ?\App\Enums\TaskType
    {
        if ($value === null || $value === '') {
            return null;
        }

        return \App\Enums\TaskType::tryFrom($value);
    }

    private function resolveBoardId(int $listId): int
    {
        $list = ListModel::findOrFail($listId);

        return (int) $list->board_id;
    }

    
    private function syncChecklist(Card $card, ?array $checklist): void
    {
        if ($checklist === null || count($checklist) === 0) {
            return;
        }

        if (isset($checklist['suggested_steps']) && is_array($checklist['suggested_steps'])) {
            $checklistTitle = $checklist['checklist_title'] ?? 'Checklist';
            $suggestedSteps = $checklist['suggested_steps'];

            $createdChecklist = $card->checklists()->create([
                'title' => $checklistTitle,
                'position' => 1,
            ]);

            foreach ($suggestedSteps as $index => $step) {
                if (! is_array($step)) {
                    continue;
                }

                $title = $step['title'] ?? null;
                if (! is_string($title) || trim($title) === '') {
                    continue;
                }

                $itemPosition = isset($step['position']) && is_int($step['position'])
                    ? $step['position']
                    : $index + 1;

                $createdChecklist->items()->create([
                    'title' => trim($title),
                    'position' => $itemPosition,
                    'is_completed' => false,
                ]);
            }

            return;
        }

        $createdChecklist = $card->checklists()->create([
            'title' => 'Чеклист',
            'position' => 1,
        ]);

        $position = 1;
        
        foreach ($checklist as $itemTitle) {
            if (is_string($itemTitle) && trim($itemTitle) !== '') {
                $createdChecklist->items()->create([
                    'title' => trim($itemTitle),
                    'position' => $position,
                    'is_completed' => false,
                ]);
                $position++;
            }
        }
    }
}
