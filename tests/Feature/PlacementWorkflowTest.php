<?php

namespace Tests\Feature;

use App\Enums\BillingType;
use App\Enums\CloseReason;
use App\Enums\NoteType;
use App\Enums\PlacementStage;
use App\Enums\TaskStatus;
use App\Models\Client;
use App\Models\Placement;
use App\Models\TaskTemplate;
use App\Models\Therapist;
use Database\Seeders\TaskTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlacementWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TaskTemplateSeeder::class);
    }

    public function test_a_new_case_gets_a_reference_its_checklist_and_a_timeline_entry(): void
    {
        $placement = Client::factory()->create()->placements()->create();

        $this->assertSame('C-'.now()->year.'-0001', $placement->reference);
        $this->assertSame(PlacementStage::New, $placement->stage);
        $this->assertSame(3, $placement->tasks()->count());
        $this->assertSame(NoteType::System, $placement->notes()->first()->type);
    }

    public function test_moving_back_and_forth_does_not_duplicate_open_tasks(): void
    {
        $placement = Client::factory()->create()->placements()->create();

        $placement->update(['stage' => PlacementStage::OnboardingCall]);
        $placement->update(['stage' => PlacementStage::New]);
        $placement->update(['stage' => PlacementStage::OnboardingCall]);

        $this->assertSame(5, $placement->tasks()->count());
    }

    public function test_finished_checklist_is_created_again_when_a_stage_is_reentered(): void
    {
        $placement = Client::factory()->create()->placements()->create(['stage' => PlacementStage::Matching]);
        $placement->tasks()->update(['status' => TaskStatus::Done]);

        $placement->update(['stage' => PlacementStage::Proposed]);
        $placement->update(['stage' => PlacementStage::Matching]);

        $matchingTemplates = TaskTemplate::query()->where('stage', PlacementStage::Matching)->pluck('id');

        $this->assertSame(3, $placement->tasks()->notDone()->whereIn('task_template_id', $matchingTemplates)->count());
    }

    public function test_first_session_billing_is_created_when_the_decision_is_pending(): void
    {
        $therapist = Therapist::factory()->create(['first_session_fee' => 120]);
        $placement = Client::factory()->create()->placements()->create();

        $placement->update(['stage' => PlacementStage::DecisionPending, 'therapist_id' => $therapist->id]);

        $items = $placement->billingItems()->get()->keyBy(fn ($item) => $item->type->value);
        $this->assertEquals(180, $items[BillingType::FirstSession->value]->amount);
        $this->assertSame($placement->client->company_id, $items[BillingType::FirstSession->value]->company_id);
        $this->assertEquals(120, $items[BillingType::TherapistFee->value]->amount);
        $this->assertSame($therapist->id, $items[BillingType::TherapistFee->value]->therapist_id);
    }

    public function test_a_rematch_with_another_therapist_bills_the_first_session_again(): void
    {
        [$first, $second] = Therapist::factory()->count(2)->create();
        $placement = Client::factory()->create()->placements()->create();

        $placement->update(['stage' => PlacementStage::DecisionPending, 'therapist_id' => $first->id]);
        $placement->update(['stage' => PlacementStage::Matching]);
        $placement->update(['stage' => PlacementStage::DecisionPending, 'therapist_id' => $second->id]);
        $placement->update(['stage' => PlacementStage::Matching]);
        $placement->update(['stage' => PlacementStage::DecisionPending]);

        $this->assertSame(2, $placement->billingItems()->where('type', BillingType::FirstSession)->count());
        $this->assertSame(2, $placement->billingItems()->where('type', BillingType::TherapistFee)->count());
    }

    public function test_closing_after_the_introduction_started_bills_the_success_fee_once(): void
    {
        $placement = Client::factory()->create()->placements()->create([
            'therapist_id' => Therapist::factory()->create()->id,
        ]);

        $placement->update(['stage' => PlacementStage::Closed, 'close_reason' => CloseReason::IntroductionStarted]);
        $placement->update(['close_reason' => CloseReason::Other]);
        $placement->update(['close_reason' => CloseReason::IntroductionStarted]);

        $this->assertNotNull($placement->closed_at);
        $this->assertSame(1, $placement->billingItems()->where('type', BillingType::SuccessFee)->count());
        $this->assertEquals(450, $placement->billingItems()->where('type', BillingType::SuccessFee)->value('amount'));
    }

    public function test_closing_for_other_reasons_bills_no_success_fee(): void
    {
        $placement = Client::factory()->create()->placements()->create();

        $placement->update(['stage' => PlacementStage::Closed, 'close_reason' => CloseReason::ClientWithdrew]);

        $this->assertSame(0, $placement->billingItems()->count());
    }

    public function test_reopening_a_case_clears_the_close_data(): void
    {
        $placement = Client::factory()->create()->placements()->create();
        $placement->update(['stage' => PlacementStage::Closed, 'close_reason' => CloseReason::ClientUnreachable]);

        $placement->update(['stage' => PlacementStage::Matching]);

        $this->assertNull($placement->closed_at);
        $this->assertNull($placement->close_reason);
    }

    public function test_references_count_up(): void
    {
        $client = Client::factory()->create();

        $client->placements()->create();
        $second = $client->placements()->create();

        $this->assertSame('C-'.now()->year.'-0002', $second->reference);
        $this->assertSame(2, Placement::query()->count());
    }
}
