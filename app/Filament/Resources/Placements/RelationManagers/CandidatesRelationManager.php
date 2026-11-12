<?php

namespace App\Filament\Resources\Placements\RelationManagers;

use App\Enums\CandidateStatus;
use App\Filament\Resources\Placements\PlacementResource;
use App\Filament\Resources\Therapists\TherapistResource;
use App\Models\Placement;
use App\Models\PlacementCandidate;
use App\Models\Therapist;
use App\Services\TherapistMatcher;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

/**
 * Therapists considered for this case. The picker only lists therapists
 * matching the client's preferences unless "show all" is switched on.
 */
class CandidatesRelationManager extends RelationManager
{
    protected static string $relationship = 'candidates';

    protected static ?string $title = 'Kandidat:innen';

    protected static ?string $modelLabel = 'Kandidat:in';

    protected static string|\BackedEnum|null $icon = Heroicon::OutlinedHeart;

    /**
     * Editable on the record's view page too, where the team works.
     */
    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('show_all')
                    ->label('Alle Therapeut:innen zeigen, nicht nur passende')
                    ->dehydrated(false)
                    ->live()
                    ->hiddenOn('edit'),
                Select::make('therapist_id')
                    ->label('Therapeut:in')
                    ->options(fn (Get $get): array => $this->therapistOptions((bool) $get('show_all')))
                    ->searchable()
                    ->required()
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule): Unique => $rule->where('placement_id', $this->getOwnerRecord()->getKey()))
                    ->hiddenOn('edit'),
                Select::make('status')
                    ->options(CandidateStatus::class)
                    ->default(CandidateStatus::Contacted)
                    ->required(),
                TextInput::make('note')
                    ->placeholder('z. B. freier Platz dienstags ab Dez.'),
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('therapist.last_name')
            ->columns([
                TextColumn::make('therapist.last_name')
                    ->label('Therapeut:in')
                    ->formatStateUsing(fn (PlacementCandidate $record): string => $record->therapist->full_name)
                    ->description(fn (PlacementCandidate $record): ?string => $record->therapist->city)
                    ->url(fn (PlacementCandidate $record): string => TherapistResource::getUrl('view', ['record' => $record->therapist_id]))
                    ->color('primary'),
                TextColumn::make('therapist.capacity')
                    ->label('Kapazität')
                    ->badge(),
                SelectColumn::make('status')
                    ->options(CandidateStatus::class)
                    ->selectablePlaceholder(false),
                TextColumn::make('note')
                    ->wrap()
                    ->placeholder('-'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Therapeut:in hinzufügen'),
            ])
            ->recordActions([
                Action::make('assign')
                    ->label('Dem Fall zuweisen')
                    ->icon(Heroicon::OutlinedCheckBadge)
                    ->color('success')
                    ->requiresConfirmation()
                    ->hidden(fn (PlacementCandidate $record): bool => $this->getOwnerRecord()->therapist_id === $record->therapist_id)
                    ->action(function (PlacementCandidate $record): void {
                        $record->update(['status' => CandidateStatus::Chosen]);
                        $this->getOwnerRecord()->update(['therapist_id' => $record->therapist_id]);

                        // Reload so the case details above show the new therapist.
                        $this->redirect(PlacementResource::getUrl('view', ['record' => $this->getOwnerRecord(), 'relation' => 1]));
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    /**
     * @return array<int, string>
     */
    private function therapistOptions(bool $showAll): array
    {
        /** @var Placement $placement */
        $placement = $this->getOwnerRecord();

        $query = $showAll
            ? Therapist::query()->orderBy('last_name')
            : app(TherapistMatcher::class)->query($placement->client);

        return $query
            ->whereNotIn('id', $placement->candidates()->pluck('therapist_id'))
            ->get()
            ->mapWithKeys(fn (Therapist $therapist): array => [
                $therapist->id => "{$therapist->full_name} · {$therapist->city} · {$therapist->capacity->getLabel()}",
            ])
            ->all();
    }
}
