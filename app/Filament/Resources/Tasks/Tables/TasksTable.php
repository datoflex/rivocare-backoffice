<?php

namespace App\Filament\Resources\Tasks\Tables;

use App\Enums\Responsible;
use App\Enums\TaskStatus;
use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\Placements\PlacementResource;
use App\Filament\Resources\Therapists\TherapistResource;
use App\Models\Company;
use App\Models\Placement;
use App\Models\Task;
use App\Models\Therapist;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('taskable'))
            ->defaultSort('due_on')
            ->columns([
                TextColumn::make('title')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('taskable')
                    ->label('Gehört zu')
                    ->state(fn (Task $record): ?string => static::relatedLabel($record))
                    ->url(fn (Task $record): ?string => static::relatedUrl($record))
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
                    ->sortable()
                    ->color(fn (Task $record): ?string => $record->isOverdue() ? 'danger' : null)
                    ->placeholder('-'),
                TextColumn::make('assignedUser.name')
                    ->label('Zuständig')
                    ->placeholder('-'),
            ])
            ->filters([
                Filter::make('mine')
                    ->label('Mir zugewiesen')
                    ->query(fn (Builder $query) => $query->where('assigned_user_id', auth()->id())),
                Filter::make('not_done')
                    ->label('Erledigte ausblenden')
                    ->query(fn (Builder $query) => $query->notDone())
                    ->default(),
                Filter::make('overdue')
                    ->query(fn (Builder $query) => $query->overdue()),
                SelectFilter::make('status')
                    ->options(TaskStatus::class)
                    ->multiple(),
                SelectFilter::make('responsible')
                    ->options(Responsible::class),
            ])
            ->recordActions([
                Action::make('markDone')
                    ->label('Erledigt')
                    ->icon(Heroicon::OutlinedCheck)
                    ->color('success')
                    ->hidden(fn (Task $record): bool => $record->status === TaskStatus::Done)
                    ->action(fn (Task $record) => $record->update(['status' => TaskStatus::Done])),
                EditAction::make(),
            ]);
    }

    public static function relatedLabel(Task $task): ?string
    {
        return match (true) {
            $task->taskable instanceof Placement => "{$task->taskable->reference} · {$task->taskable->client->full_name}",
            $task->taskable instanceof Company => $task->taskable->name,
            $task->taskable instanceof Therapist => $task->taskable->full_name,
            default => null,
        };
    }

    public static function relatedUrl(Task $task): ?string
    {
        return match (true) {
            $task->taskable instanceof Placement => PlacementResource::getUrl('view', ['record' => $task->taskable]),
            $task->taskable instanceof Company => CompanyResource::getUrl('view', ['record' => $task->taskable]),
            $task->taskable instanceof Therapist => TherapistResource::getUrl('view', ['record' => $task->taskable]),
            default => null,
        };
    }
}
