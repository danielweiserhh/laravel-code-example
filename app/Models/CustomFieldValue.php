<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomFieldValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_id',
        'custom_field_id',
        'value',
    ];
    
    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
    
    public function customField(): BelongsTo
    {
        return $this->belongsTo(CustomField::class);
    }
}
