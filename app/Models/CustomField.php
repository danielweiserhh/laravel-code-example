<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CustomFieldType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomField extends Model
{
    use HasFactory;

    protected $fillable = [
        'board_id',
        'name',
        'type',
        'options',
        'position',
    ];

    protected $casts = [
        'type' => CustomFieldType::class,
        'options' => 'array',
    ];
    
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }
    
    public function values(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }
}
