<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Inbox;

use App\Domain\Inbox\Repositories\InboxItemRepositoryInterface;
use App\Domain\Inbox\ValueObjects\InboxItem as DomainInboxItem;
use App\Domain\Shared\ValueObjects\PaginatedResult;
use App\Domain\Shared\ValueObjects\UserId;
use App\Models\InboxItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EloquentInboxItemRepository implements InboxItemRepositoryInterface
{
    private function toDomain(InboxItem $model): DomainInboxItem
    {
        return DomainInboxItem::fromArray([
            'id' => $model->id,
            'user_id' => $model->user_id,
            'workspace_id' => $model->workspace_id,
            'content' => $model->content,
            'source' => $model->source ?? 'manual',
            'is_processed' => $model->is_processed,
            'converted_to_card_id' => $model->converted_to_card_id,
            'ai_suggestions' => $model->ai_suggestions,
            'created_at' => $model->created_at->toIso8601String(),
            'updated_at' => $model->updated_at->toIso8601String(),
        ]);
    }
    
    private function toModelAttributes(DomainInboxItem $domain): array
    {
        return [
            'user_id' => $domain->userId,
            'workspace_id' => $domain->workspaceId,
            'content' => $domain->content,
            'source' => $domain->source,
            'is_processed' => $domain->isProcessed,
            'converted_to_card_id' => $domain->convertedToCardId,
            'ai_suggestions' => $domain->aiSuggestions,
        ];
    }
    
    private function toPaginatedResult(LengthAwarePaginator $paginator): PaginatedResult
    {
        $items = [];

        foreach ($paginator->items() as $item) {
            if ($item instanceof InboxItem) {
                $items[] = $this->toDomain($item);
            }
        }

        return new PaginatedResult(
            items: $items,
            currentPage: $paginator->currentPage(),
            perPage: $paginator->perPage(),
            total: $paginator->total(),
            lastPage: $paginator->lastPage(),
        );
    }

    public function find(int $id): ?DomainInboxItem
    {
        $model = InboxItem::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findOrFail(int $id): DomainInboxItem
    {
        $model = InboxItem::findOrFail($id);

        return $this->toDomain($model);
    }

    public function save(DomainInboxItem $item): DomainInboxItem
    {
        if ($item->id > 0) {  
            $model = InboxItem::findOrFail($item->id);
            $model->fill($this->toModelAttributes($item));
            $model->save();

            return $this->toDomain($model->fresh());
        }
        
        $model = new InboxItem($this->toModelAttributes($item));
        $model->save();

        return $this->toDomain($model->fresh());
    }
    
    public function getUnprocessedForUser(UserId $userId): array
    {
        $models = InboxItem::where('user_id', $userId->value)
            ->where(function ($query) {
                $query->where('is_processed', false)
                    ->orWhereNotNull('ai_suggestions');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $result = [];

        foreach ($models as $model) {
            $result[] = $this->toDomain($model);
        }

        return $result;
    }

    public function getUnprocessedForUserPaginated(UserId $userId, int $perPage = 10): PaginatedResult
    {
        $paginator = InboxItem::where('user_id', $userId->value)
            ->where('is_processed', false)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->toPaginatedResult($paginator);
    }

    
    public function getUnprocessedForUserExcluding(UserId $userId, array $excludedIds = [], int $limit = 10): array
    {
        $query = InboxItem::where('user_id', $userId->value)
            ->where('is_processed', false);

        if (! empty($excludedIds)) {
            $query->whereNotIn('id', $excludedIds);
        }

        $models = $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        $result = [];

        foreach ($models as $model) {
            $result[] = $this->toDomain($model);
        }

        return $result;
    }

    public function getAllForUserPaginated(UserId $userId, int $perPage = 10): PaginatedResult
    {
        $paginator = InboxItem::where('user_id', $userId->value)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->toPaginatedResult($paginator);
    }

    
    public function create(array $payload, UserId $userId): DomainInboxItem
    {
        $model = new InboxItem;
        $model->user_id = $userId->value;
        $model->content = (string) $payload['content'];
        $model->workspace_id = $payload['workspace_id'] ?? null;
        $model->source = $payload['source'] ?? 'manual';
        $model->is_processed = false;
        $model->save();

        return $this->toDomain($model->fresh());
    }

    
    public function update(int $id, array $payload): DomainInboxItem
    {
        $model = InboxItem::findOrFail($id);

        if (array_key_exists('content', $payload) && $payload['content'] !== null) {
            $model->content = (string) $payload['content'];
        }
        
        if (array_key_exists('is_processed', $payload) && $payload['is_processed'] !== null) {
            $model->is_processed = (bool) $payload['is_processed'];
        }

        $model->save();

        return $this->toDomain($model->fresh());
    }

    public function delete(int $id): bool
    {
        $model = InboxItem::findOrFail($id);

        return $model->delete();
    }

    
    public function updateAISuggestions(int $id, array $suggestions): bool
    {
        $model = InboxItem::findOrFail($id);

        return $model->update(['ai_suggestions' => $suggestions]);
    }

    public function updateConvertedToCardId(int $id, int $cardId): bool
    {
        $model = InboxItem::findOrFail($id);

        return $model->update(['converted_to_card_id' => $cardId]);
    }
}
