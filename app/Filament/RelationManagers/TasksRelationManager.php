<?php

namespace App\Filament\RelationManagers;

use App\Enums\Responsible;
use App\Enums\TaskStatus;
use App\Filament\Resources\Tasks\Schemas\TaskForm;
use App\Models\Task;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    protected static ?string $modelLabel = 'Aufgabe';

    protected static ?string $title = 'Aufgaben';

    protected static string|\BackedEnum|null $icon = Heroicon::OutlinedCheckCircle;

    /**
     * Editable on the record's view page too, where the team works.
     */
    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components(TaskForm::fields());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort(fn (Builder $query) => $query->orderByRaw("status = 'done'")->orderBy('due_on'))
            ->columns([
                TextColumn::make('title')
                    ->description(fn (Task $record): ?string => $record->description)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('responsible')
                    ->badge(),
                SelectColumn::make('status')
                    ->options(TaskStatus::class)
                    ->selectablePlaceholder(false),
                TextColumn::make('due_on')
                    ->date()
                    ->sortable()
                    ->color(fn (Task $record): ?string => $record->isOverdue() ? 'danger' : null)
                    ->placeholder('-'),
                TextColumn::make('assignedUser.name')
                    ->label('Zuständig')
                    ->placeholder('-'),
            ])
            ->filters([
                Filter::make('not_done')
                    ->label('Erledigte ausblenden')
                    ->query(fn (Builder $query) => $query->notDone())
                    ->default(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->fillForm([
                        'responsible' => Responsible::Us,
                        'status' => TaskStatus::Open,
                        'assigned_user_id' => auth()->id(),
                    ]),
            ])
            ->recordActions([
                Action::make('markDone')
                    ->label('Erledigt')
                    ->icon(Heroicon::OutlinedCheck)
                    ->color('success')
                    ->hidden(fn (Task $record): bool => $record->status === TaskStatus::Done)
                    ->action(fn (Task $record) => $record->update(['status' => TaskStatus::Done])),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
