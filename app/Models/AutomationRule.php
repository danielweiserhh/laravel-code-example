<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'user_id',
        'name',
        'description',
        'trigger_type',
        'trigger_conditions',
        'action_type',
        'action_params',
        'is_active',
    ];

    protected $casts = [
        'trigger_conditions' => 'array',
        'action_params' => 'array',
        'is_active' => 'boolean',
    ];
    
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
