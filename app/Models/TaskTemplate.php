<?php

namespace App\Models;

use App\Enums\PlacementStage;
use App\Enums\Responsible;
use App\Models\Concerns\AuditsChanges;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * A task that is created automatically when a placement enters a stage.
 */
#[Fillable(['stage', 'title', 'description', 'responsible', 'due_in_days', 'sort', 'is_active'])]
class TaskTemplate extends Model
{
    use AuditsChanges;

    protected function casts(): array
    {
        return [
            'stage' => PlacementStage::class,
            'responsible' => Responsible::class,
            'is_active' => 'boolean',
        ];
    }
}
