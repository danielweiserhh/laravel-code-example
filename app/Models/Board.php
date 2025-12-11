<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BoardPrivacy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Board extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'name',
        'description',
        'color',
        'privacy',
        'is_favorite',
        'settings',
        'position',
    ];

    protected $hidden = [];

    protected $casts = [
        'privacy' => BoardPrivacy::class,
        'is_favorite' => 'boolean',
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
    
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'board_members')
            ->withPivot('role')
            ->withTimestamps();
    }
    
    public function lists(): HasMany
    {
        return $this->hasMany(ListModel::class)->orderBy('position');
    }
    
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    public function customFields(): HasMany
    {
        return $this->hasMany(CustomField::class);
    }
}
