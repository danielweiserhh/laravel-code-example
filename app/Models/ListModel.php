<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ListModel extends Model
{
    use HasFactory;

    protected $table = 'lists';

    protected $fillable = [
        'board_id',
        'name',
        'position',
        'is_archived',
    ];

    protected $hidden = [];

    protected $casts = [
        'is_archived' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }
    
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class, 'list_id')->orderBy('position');
    }
}
