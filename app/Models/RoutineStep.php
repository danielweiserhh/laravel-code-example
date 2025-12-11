<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutineStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'routine_id',
        'title',
        'description',
        'position',
        'duration_minutes',
    ];

    protected $casts = [
        'position' => 'integer',
        'duration_minutes' => 'integer',
    ];
    
    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class);
    }
}
