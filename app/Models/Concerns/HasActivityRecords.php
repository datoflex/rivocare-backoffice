<?php

namespace App\Models\Concerns;

use App\Models\Document;
use App\Models\Note;
use App\Models\Task;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Tasks, timeline notes and documents that can be attached to a record.
 */
trait HasActivityRecords
{
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable')->latest('occurred_at');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
