<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workspace extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'slug',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];
    
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_members')
            ->withPivot('role')
            ->withTimestamps();
    }
    
    public function boards(): HasMany
    {
        return $this->hasMany(Board::class);
    }
    
    public function routines(): HasMany
    {
        return $this->hasMany(Routine::class);
    }
    
    public function automationRules(): HasMany
    {
        return $this->hasMany(AutomationRule::class);
    }
    
    public function integrations(): HasMany
    {
        return $this->hasMany(Integration::class);
    }
    
    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class);
    }
}
