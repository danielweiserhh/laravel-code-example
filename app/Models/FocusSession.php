<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FocusSessionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FocusSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'card_id',
        'duration_minutes',
        'started_at',
        'ended_at',
        'status',
        'is_group',
        'video_link',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'status' => FocusSessionStatus::class,
        'is_group' => 'boolean',
        'duration_minutes' => 'integer',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
    
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'focus_session_participants')
            ->withPivot('joined_at')
            ->withTimestamps();
    }
}
