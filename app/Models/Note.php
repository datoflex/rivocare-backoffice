<?php

namespace App\Models;

use App\Enums\NoteType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A timeline entry: a note, a logged call or email, or a system event.
 */
#[Fillable(['notable_type', 'notable_id', 'user_id', 'type', 'body', 'occurred_at'])]
class Note extends Model
{
    protected function casts(): array
    {
        return [
            'type' => NoteType::class,
            'body' => 'encrypted',
            'occurred_at' => 'datetime',
        ];
    }

    public function notable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
