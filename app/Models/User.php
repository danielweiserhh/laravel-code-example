<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'phone',
        'telegram_username',
        'ai_model',
        'speech_model',
    ];
    
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    public function workspaces(): BelongsToMany
    {
        return $this->belongsToMany(Workspace::class, 'workspace_members')
            ->withPivot('role')
            ->withTimestamps();
    }
    
    public function boards(): BelongsToMany
    {
        return $this->belongsToMany(Board::class, 'board_members')
            ->withPivot('role')
            ->withTimestamps();
    }
    
    public function assignedCards(): BelongsToMany
    {
        return $this->belongsToMany(Card::class, 'card_assignees')
            ->withTimestamps();
    }
    
    public function routines(): HasMany
    {
        return $this->hasMany(Routine::class);
    }
    
    public function focusSessions(): HasMany
    {
        return $this->hasMany(FocusSession::class);
    }
    
    public function inboxItems(): HasMany
    {
        return $this->hasMany(InboxItem::class);
    }
    
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
    
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }
    
    public function aiJobs(): HasMany
    {
        return $this->hasMany(AIJob::class);
    }
}
