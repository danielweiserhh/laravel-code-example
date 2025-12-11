<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AIJobType;
use App\Enums\JobStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIJob extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'ai_jobs';

    protected $fillable = [
        'user_id',
        'workspace_id',
        'type',
        'payload',
        'status',
        'result',
        'error_message',
    ];

    protected $casts = [
        'type' => AIJobType::class,
        'status' => JobStatus::class,
        'payload' => 'array',
        'result' => 'array',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
