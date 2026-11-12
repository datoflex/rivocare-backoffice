<?php

namespace App\Services;

use App\Enums\BillingType;
use App\Enums\CloseReason;
use App\Enums\NoteType;
use App\Enums\PlacementStage;
use App\Enums\TaskStatus;
use App\Models\BillingItem;
use App\Models\Placement;
use App\Models\TaskTemplate;

/**
 * Side effects of a placement moving through the pipeline:
 * timeline entries, checklist tasks from templates, and billing items.
 */
class PlacementWorkflow
{
    public function enteredStage(Placement $placement, ?PlacementStage $from): void
    {
        $placement->notes()->create([
            'user_id' => auth()->id(),
            'type' => NoteType::System,
            'body' => $from === null
                ? "Fall eröffnet in Phase „{$placement->stage->getLabel()}“."
                : "Phase geändert von „{$from->getLabel()}“ zu „{$placement->stage->getLabel()}“.",
            'occurred_at' => now(),
        ]);

        $this->createTasksFromTemplates($placement);

        if ($placement->stage === PlacementStage::DecisionPending) {
            $this->createFirstSessionBilling($placement);
        }

        if ($placement->stage === PlacementStage::Closed) {
            $this->closeReasonChanged($placement);
        }
    }

    public function closeReasonChanged(Placement $placement): void
    {
        if ($placement->close_reason !== CloseReason::IntroductionStarted) {
            return;
        }

        $company = $placement->client->company;

        BillingItem::query()->firstOrCreate(
            ['placement_id' => $placement->id, 'type' => BillingType::SuccessFee],
            ['company_id' => $company->id, 'amount' => $company->success_fee],
        );
    }

    /**
     * Creates the stage's checklist. A template is skipped while an
     * unfinished task from it still exists, so moving a card back and
     * forth doesn't pile up duplicates.
     */
    private function createTasksFromTemplates(Placement $placement): void
    {
        $openTemplateIds = $placement->tasks()
            ->where('status', '!=', TaskStatus::Done)
            ->whereNotNull('task_template_id')
            ->pluck('task_template_id');

        TaskTemplate::query()
            ->where('stage', $placement->stage)
            ->where('is_active', true)
            ->whereNotIn('id', $openTemplateIds)
            ->orderBy('sort')
            ->each(fn (TaskTemplate $template) => $placement->tasks()->create([
                'task_template_id' => $template->id,
                'assigned_user_id' => $placement->assigned_user_id,
                'title' => $template->title,
                'description' => $template->description,
                'responsible' => $template->responsible,
                'status' => TaskStatus::Open,
                'due_on' => $template->due_in_days !== null ? today()->addDays($template->due_in_days) : null,
            ]));
    }

    /**
     * The company pays us for the first session and we pay the therapist.
     * Keyed by therapist, so a rematch with another therapist bills again.
     */
    private function createFirstSessionBilling(Placement $placement): void
    {
        // Queried fresh: a loaded relation can still hold the previous therapist after a rematch.
        $therapist = $placement->therapist()->first();

        if ($therapist === null) {
            return;
        }

        $company = $placement->client->company;

        BillingItem::query()->firstOrCreate(
            ['placement_id' => $placement->id, 'type' => BillingType::FirstSession, 'therapist_id' => $therapist->id],
            ['company_id' => $company->id, 'amount' => $company->first_session_price],
        );

        BillingItem::query()->firstOrCreate(
            ['placement_id' => $placement->id, 'type' => BillingType::TherapistFee, 'therapist_id' => $therapist->id],
            ['amount' => $therapist->first_session_fee],
        );
    }
}
