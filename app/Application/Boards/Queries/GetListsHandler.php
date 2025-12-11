<?php

declare(strict_types=1);

namespace App\Application\Boards\Queries;

use App\Domain\Boards\Repositories\ListRepositoryInterface;
use App\Models\ListModel;
use Illuminate\Support\Collection;

final class GetListsHandler
{
    public function __construct(
        private readonly ListRepositoryInterface $listRepository
    ) {}

    public function handle(GetListsQuery $query): Collection
    {
        $domainLists = $this->listRepository->getByBoard($query->boardId);

        if (! $query->includeArchived) {
            $domainLists = array_filter($domainLists, fn ($list) => ! $list->isArchived);
        }

        $listIds = array_map(fn ($list) => $list->id, $domainLists);

        $lists = ListModel::whereIn('id', $listIds)
            ->withCount(['cards as active_cards_count' => function ($q) {
                $q->where('is_completed', false);
            }])
            ->get()
            ->keyBy('id');

        return Collection::make($domainLists)
            ->map(fn ($domainList) => $lists->get($domainList->id))
            ->filter()
            ->sortBy('position')
            ->values();
    }
}
