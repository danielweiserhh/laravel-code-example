<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AttachmentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_id',
        'user_id',
        'name',
        'path',
        'mime_type',
        'size',
        'type',
        'url',
    ];

    protected $casts = [
        'type' => AttachmentType::class,
        'size' => 'integer',
    ];

    
    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    
    public function coverCards(): HasMany
    {
        return $this->hasMany(Card::class, 'cover_attachment_id');
    }
}
