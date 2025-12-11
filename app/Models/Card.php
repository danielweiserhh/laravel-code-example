<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EnergyLevel;
use App\Enums\TaskType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'list_id',
        'board_id',
        'title',
        'description',
        'position',
        'start_date',
        'due_date',
        'energy_level',
        'task_type',
        'is_completed',
        'completed_at',
        'cover_attachment_id',
        'custom_fields',
    ];

    protected $hidden = [
        
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'energy_level' => EnergyLevel::class,
        'task_type' => TaskType::class,
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'custom_fields' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    public function list(): BelongsTo
    {
        return $this->belongsTo(ListModel::class, 'list_id');
    }
    
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }
    
    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'card_assignees')
            ->withTimestamps();
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(Checklist::class)->orderBy('position');
    }
    
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }
    
    public function coverAttachment(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'cover_attachment_id');
    }
    
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->orderBy('created_at');
    }
    
    public function customFieldValues(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }
    
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }
    
    public function focusSessions(): HasMany
    {
        return $this->hasMany(FocusSession::class);
    }
}
