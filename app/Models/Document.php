<?php

namespace App\Models;

use App\Models\Concerns\AuditsChanges;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

/**
 * A file stored on the private `local` disk. Files are only served
 * through the authenticated download route, never publicly.
 */
#[Fillable(['documentable_type', 'documentable_id', 'uploaded_by_id', 'title', 'path', 'original_name'])]
class Document extends Model
{
    use AuditsChanges;

    public const DISK = 'local';

    protected static function booted(): void
    {
        static::deleted(function (Document $document): void {
            Storage::disk(self::DISK)->delete($document->path);
        });
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }
}
