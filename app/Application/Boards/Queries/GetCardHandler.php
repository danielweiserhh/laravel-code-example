<?php

declare(strict_types=1);

namespace App\Application\Boards\Queries;

use App\Models\Card;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class GetCardHandler
{
    
    public function handle(GetCardQuery $query): Card
    {
        return Card::with([
            'list',
            'board',
            'assignees',
            'checklists.items',
            'attachments',
            'comments.user',
            'customFieldValues.customField',
            'activities.user',
        ])->findOrFail($query->cardId);
    }
}
