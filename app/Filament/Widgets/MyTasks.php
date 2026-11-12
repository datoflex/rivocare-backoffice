<?php

namespace App\Filament\Widgets;

use App\Enums\TaskStatus;
use App\Filament\Resources\Tasks\Tables\TasksTable;
use App\Models\Task;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * Open tasks assigned to the signed-in user, oldest due date first.
 */
class MyTasks extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Meine offenen Aufgaben';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Task::query()
                ->with('taskable')
                ->notDone()
                ->where('assigned_user_id', auth()->id())
                ->orderByRaw('due_on is null')
                ->orderBy('due_on'))
            ->paginated([10, 25])
            ->emptyStateHeading('Nichts auf deiner Liste')
            ->columns([
                TextColumn::make('title')
                    ->wrap(),
                TextColumn::make('taskable')
                    ->label('Gehört zu')
                    ->state(fn (Task $record): ?string => TasksTable::relatedLabel($record))
                    ->url(fn (Task $record): ?string => TasksTable::relatedUrl($record))
                    ->color('primary')
                    ->placeholder('-'),
                TextColumn::make('responsible')
                    ->badge(),
                SelectColumn::make('status')
                    ->options(TaskStatus::class)
                    ->selectablePlaceholder(false),
                TextColumn::make('due_on')
                    ->label('Fällig')
                    ->date()
                    ->color(fn (Task $record): ?string => $record->isOverdue() ? 'danger' : null)
                    ->placeholder('-'),
            ])
            ->recordActions([
                Action::make('markDone')
                    ->label('Erledigt')
                    ->icon(Heroicon::OutlinedCheck)
                    ->color('success')
                    ->action(fn (Task $record) => $record->update(['status' => TaskStatus::Done])),
            ]);
    }
}
