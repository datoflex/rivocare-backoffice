<?php

namespace App\Models;

use App\Enums\CandidateStatus;
use App\Models\Concerns\AuditsChanges;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A therapist considered for a placement, and how far that went.
 */
#[Fillable(['placement_id', 'therapist_id', 'status', 'note'])]
class PlacementCandidate extends Model
{
    use AuditsChanges;

    protected function casts(): array
    {
        return [
            'status' => CandidateStatus::class,
        ];
    }

    public function placement(): BelongsTo
    {
        return $this->belongsTo(Placement::class);
    }

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(Therapist::class);
    }
}
