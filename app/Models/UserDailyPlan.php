<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDailyPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_date',
        'big_three',
        'note_for_user',
        'ai_job_id',
    ];

    protected $casts = [
        'plan_date' => 'date',
        'big_three' => 'array',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function aiJob(): BelongsTo
    {
        return $this->belongsTo(AIJob::class, 'ai_job_id');
    }
}
