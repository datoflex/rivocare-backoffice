<?php

namespace Tests\Feature;

use App\Enums\PlacementStage;
use App\Filament\Pages\Pipeline;
use App\Filament\RelationManagers\DocumentsRelationManager;
use App\Filament\RelationManagers\NotesRelationManager;
use App\Filament\RelationManagers\TasksRelationManager;
use App\Filament\Resources\Clients\Pages\ViewClient;
use App\Filament\Resources\Clients\RelationManagers\PlacementsRelationManager;
use App\Filament\Resources\Companies\Pages\ViewCompany;
use App\Filament\Resources\Companies\RelationManagers\ClientsRelationManager;
use App\Filament\Resources\Companies\RelationManagers\ContactsRelationManager;
use App\Filament\Resources\Placements\Pages\ViewPlacement;
use App\Filament\Resources\Placements\RelationManagers\BillingItemsRelationManager;
use App\Filament\Resources\Placements\RelationManagers\CandidatesRelationManager;
use App\Filament\Widgets\MyTasks;
use App\Filament\Widgets\OverviewStats;
use App\Filament\Widgets\StaleCases;
use App\Models\Client;
use App\Models\Placement;
use App\Models\Therapist;
use App\Models\User;
use Database\Seeders\TaskTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BackofficePagesTest extends TestCase
{
    use RefreshDatabase;

    private Placement $placement;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TaskTemplateSeeder::class);
        $this->actingAs(User::factory()->admin()->create());

        $this->placement = Client::factory()->create()->placements()->create();
    }

    public function test_case_relation_tabs_render(): void
    {
        foreach ([TasksRelationManager::class, CandidatesRelationManager::class, NotesRelationManager::class, DocumentsRelationManager::class, BillingItemsRelationManager::class] as $relationManager) {
            Livewire::test($relationManager, ['ownerRecord' => $this->placement, 'pageClass' => ViewPlacement::class])
                ->assertSuccessful();
        }
    }

    public function test_company_and_client_relation_tabs_render(): void
    {
        $client = $this->placement->client;

        foreach ([ContactsRelationManager::class, ClientsRelationManager::class, TasksRelationManager::class] as $relationManager) {
            Livewire::test($relationManager, ['ownerRecord' => $client->company, 'pageClass' => ViewCompany::class])
                ->assertSuccessful();
        }

        Livewire::test(PlacementsRelationManager::class, ['ownerRecord' => $client, 'pageClass' => ViewClient::class])
            ->assertSuccessful()
            ->assertSee($this->placement->reference);
    }

    public function test_dashboard_widgets_render(): void
    {
        Livewire::test(OverviewStats::class)->assertSuccessful();
        Livewire::test(MyTasks::class)->assertSuccessful()->assertSee('Willkommens-E-Mail mit Einwilligungserklärung senden');
        Livewire::test(StaleCases::class)->assertSuccessful();
    }

    public function test_the_board_refuses_to_close_a_case_by_dragging(): void
    {
        Livewire::test(Pipeline::class)
            ->call('moveCard', (string) $this->placement->id, PlacementStage::Closed->value)
            ->assertNotified();

        $this->assertSame(PlacementStage::New, $this->placement->fresh()->stage);
    }

    public function test_the_board_needs_a_therapist_for_the_first_session(): void
    {
        Livewire::test(Pipeline::class)
            ->call('moveCard', (string) $this->placement->id, PlacementStage::FirstSessionScheduled->value);

        $this->assertSame(PlacementStage::New, $this->placement->fresh()->stage);

        $this->placement->update(['therapist_id' => Therapist::factory()->create()->id]);

        Livewire::test(Pipeline::class)
            ->call('moveCard', (string) $this->placement->id, PlacementStage::FirstSessionScheduled->value);

        $this->assertSame(PlacementStage::FirstSessionScheduled, $this->placement->fresh()->stage);
    }

    public function test_the_change_stage_action_requires_a_close_reason(): void
    {
        Livewire::test(ViewPlacement::class, ['record' => $this->placement->id])
            ->callAction('changeStage', ['stage' => PlacementStage::Closed->value])
            ->assertHasFormErrors(['close_reason' => 'required']);

        Livewire::test(ViewPlacement::class, ['record' => $this->placement->id])
            ->callAction('changeStage', ['stage' => PlacementStage::Closed->value, 'close_reason' => 'client_withdrew'])
            ->assertHasNoFormErrors();

        $this->assertSame(PlacementStage::Closed, $this->placement->fresh()->stage);
    }
}
