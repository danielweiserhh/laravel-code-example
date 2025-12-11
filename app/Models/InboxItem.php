<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InboxItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'workspace_id',
        'content',
        'source',
        'is_processed',
        'converted_to_card_id',
        'ai_suggestions',
    ];

    protected $casts = [
        'is_processed' => 'boolean',
        'ai_suggestions' => 'array',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
    
    public function convertedToCard(): BelongsTo
    {
        return $this->belongsTo(Card::class, 'converted_to_card_id');
    }
}
