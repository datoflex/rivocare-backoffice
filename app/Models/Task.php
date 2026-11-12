<?php

namespace App\Models;

use App\Enums\Responsible;
use App\Enums\TaskStatus;
use App\Models\Concerns\AuditsChanges;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'taskable_type', 'taskable_id', 'task_template_id', 'assigned_user_id', 'title', 'description',
    'responsible', 'status', 'due_on', 'completed_at',
])]
class Task extends Model
{
    use AuditsChanges, HasFactory;

    /**
     * Keeps the parent's `updated_at` current, which the stale-cases list relies on.
     */
    protected $touches = ['taskable'];

    protected static function booted(): void
    {
        static::saving(function (Task $task): void {
            if ($task->isDirty('status')) {
                $task->completed_at = $task->status === TaskStatus::Done ? now() : null;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'responsible' => Responsible::class,
            'status' => TaskStatus::class,
            'due_on' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function isOverdue(): bool
    {
        return $this->status !== TaskStatus::Done && $this->due_on?->isBefore(today());
    }

    public function scopeNotDone(Builder $query): void
    {
        $query->where('status', '!=', TaskStatus::Done);
    }

    public function scopeOverdue(Builder $query): void
    {
        $query->notDone()->whereDate('due_on', '<', today());
    }

    public function taskable(): MorphTo
    {
        return $this->morphTo();
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(TaskTemplate::class, 'task_template_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
}
