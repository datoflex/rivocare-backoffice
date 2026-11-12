<?php

namespace App\Filament\Pages;

use App\Enums\PlacementStage;
use App\Filament\Actions\ChangeStageAction;
use App\Filament\Resources\Placements\PlacementResource;
use App\Models\Placement;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Relaticle\Flowforge\Board;
use Relaticle\Flowforge\Column;
use Relaticle\Flowforge\Exceptions\CardMoveRejectedException;
use Relaticle\Flowforge\Modern\BoardPage;

/**
 * Kanban board of all cases. Closed cases stay visible for 30 days.
 */
class Pipeline extends BoardPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedViewColumns;

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Pipeline';

    public function board(Board $board): Board
    {
        return $board
            ->query(
                Placement::query()
                    ->with(['client.company', 'therapist', 'assignedUser'])
                    ->withCount(['tasks as open_tasks_count' => fn (Builder $query) => $query->notDone()])
                    ->withCount(['tasks as overdue_tasks_count' => fn (Builder $query) => $query->overdue()])
                    ->where(fn (Builder $query) => $query
                        ->where('stage', '!=', PlacementStage::Closed)
                        ->orWhere('closed_at', '>=', now()->subDays(30)))
            )
            ->recordTitleAttribute('reference')
            ->columnIdentifier('stage')
            ->positionIdentifier('position')
            ->columns(collect(PlacementStage::cases())
                ->map(fn (PlacementStage $stage): Column => Column::make($stage->value)
                    ->label($stage->getLabel())
                    ->color($stage->getColor()))
                ->all())
            ->cardSchema(fn (Schema $schema): Schema => $schema->components([
                TextEntry::make('client.full_name')
                    ->hiddenLabel()
                    ->weight('bold'),
                TextEntry::make('client.company.name')
                    ->hiddenLabel()
                    ->icon(Heroicon::OutlinedBuildingOffice2)
                    ->color('gray'),
                TextEntry::make('therapist.full_name')
                    ->hiddenLabel()
                    ->icon(Heroicon::OutlinedHeart)
                    ->color('gray')
                    ->visible(fn (Placement $record): bool => $record->therapist_id !== null),
                TextEntry::make('close_reason')
                    ->hiddenLabel()
                    ->badge()
                    ->visible(fn (Placement $record): bool => $record->close_reason !== null),
                TextEntry::make('tasks_summary')
                    ->hiddenLabel()
                    ->state(fn (Placement $record): string => $record->overdue_tasks_count > 0
                        ? "{$record->open_tasks_count} offen · {$record->overdue_tasks_count} überfällig"
                        : ($record->open_tasks_count === 1 ? '1 offene Aufgabe' : "{$record->open_tasks_count} offene Aufgaben"))
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color(fn (Placement $record): string => $record->overdue_tasks_count > 0 ? 'danger' : 'gray')
                    ->visible(fn (Placement $record): bool => $record->open_tasks_count > 0),
                TextEntry::make('assignedUser.name')
                    ->hiddenLabel()
                    ->icon(Heroicon::OutlinedUserCircle)
                    ->color('gray')
                    ->size('xs'),
            ]))
            ->cardAction('open')
            ->recordActions([
                Action::make('open')
                    ->label('Fall öffnen')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Placement $record): string => PlacementResource::getUrl('view', ['record' => $record])),
                ChangeStageAction::make(),
            ])
            ->searchable(['reference'])
            ->filters([
                SelectFilter::make('assigned_user_id')
                    ->label('Zuständig')
                    ->relationship('assignedUser', 'name'),
                SelectFilter::make('company')
                    ->relationship('client.company', 'name')
                    ->searchable()
                    ->preload(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('Neuer Fall')
                ->icon(Heroicon::OutlinedPlus)
                ->url(PlacementResource::getUrl('create')),
        ];
    }

    /**
     * Dragging can't ask for input, so stages that need it are refused
     * with a hint to use the "Change stage" action instead.
     */
    protected function authorizeCardMove(Model $card, string $targetColumnId, ?string $toLaneId = null): void
    {
        $stage = PlacementStage::from($targetColumnId);

        if ($stage === PlacementStage::Closed) {
            throw CardMoveRejectedException::because('Zum Abschließen „Phase ändern“ auf der Karte nutzen, damit der Abschlussgrund erfasst wird.');
        }

        if ($stage->requiresTherapist() && $card->therapist_id === null) {
            throw CardMoveRejectedException::because("Bitte zuerst eine Therapeut:in zuweisen, bevor der Fall nach „{$stage->getLabel()}“ verschoben wird.");
        }
    }
}
