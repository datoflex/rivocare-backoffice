<?php

namespace App\Observers;

use App\Enums\PlacementStage;
use App\Models\Placement;
use App\Services\PlacementWorkflow;
use Relaticle\Flowforge\Services\DecimalPosition;

class PlacementObserver
{
    public function __construct(private PlacementWorkflow $workflow) {}

    public function creating(Placement $placement): void
    {
        $placement->reference ??= Placement::nextReference();
        $placement->stage ??= PlacementStage::New;
        $placement->assigned_user_id ??= auth()->id();
        $placement->position ??= $this->positionAtEndOf($placement->stage);
    }

    public function created(Placement $placement): void
    {
        $this->workflow->enteredStage($placement, null);
    }

    public function updating(Placement $placement): void
    {
        if (! $placement->isDirty('stage')) {
            return;
        }

        // The board sets a position itself; other stage changes go to the end of the column.
        if (! $placement->isDirty('position')) {
            $placement->position = $this->positionAtEndOf($placement->stage);
        }

        $placement->closed_at = $placement->stage === PlacementStage::Closed ? now() : null;

        if ($placement->stage !== PlacementStage::Closed) {
            $placement->close_reason = null;
        }
    }

    public function updated(Placement $placement): void
    {
        if ($placement->wasChanged('stage')) {
            $this->workflow->enteredStage($placement, $placement->getOriginal('stage'));
        } elseif ($placement->wasChanged('close_reason')) {
            $this->workflow->closeReasonChanged($placement);
        }
    }

    private function positionAtEndOf(PlacementStage $stage): string
    {
        $last = Placement::query()
            ->where('stage', $stage)
            ->whereNotNull('position')
            ->orderByDesc('position')
            ->value('position');

        return $last === null
            ? DecimalPosition::forEmptyColumn()
            : DecimalPosition::after(DecimalPosition::normalize($last));
    }
}
