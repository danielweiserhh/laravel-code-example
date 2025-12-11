<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutineExecution extends Model
{
    protected $fillable = [
        'routine_id',
        'user_id',
        'date',
        'completed_steps',
        'is_completed',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'completed_steps' => 'array',
        'is_completed' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'date' => 'date',
    ];
    
    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class);
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function completeStep(int $stepPosition): void
    {
        $steps = $this->completed_steps ?? [];

        if (! in_array($stepPosition, $steps, true)) {
            $steps[] = $stepPosition;
            sort($steps);
            $this->completed_steps = $steps;
        }
    }
    
    public function getCompletedCount(): int
    {
        return count($this->completed_steps ?? []);
    }
}
