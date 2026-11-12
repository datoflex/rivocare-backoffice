<?php

namespace App\Filament\Resources\Placements\Tables;

use App\Enums\CloseReason;
use App\Enums\PlacementStage;
use App\Filament\Actions\ChangeStageAction;
use App\Models\Placement;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PlacementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['client.company', 'therapist', 'assignedUser']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('client.last_name')
                    ->label('Klient:in')
                    ->formatStateUsing(fn (Placement $record): string => $record->client->full_name)
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('client.company.name')
                    ->label('Unternehmen'),
                TextColumn::make('stage')
                    ->badge()
                    ->sortable(),
                TextColumn::make('therapist.last_name')
                    ->label('Therapeut:in')
                    ->formatStateUsing(fn (Placement $record): ?string => $record->therapist?->full_name)
                    ->placeholder('-'),
                TextColumn::make('assignedUser.name')
                    ->label('Zuständig')
                    ->placeholder('-'),
                TextColumn::make('close_reason')
                    ->badge()
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Eröffnet')
                    ->date()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Letzte Änderung')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('open')
                    ->label('Nur offene Fälle')
                    ->query(fn (Builder $query) => $query->open())
                    ->default(),
                SelectFilter::make('stage')
                    ->options(PlacementStage::class)
                    ->multiple(),
                SelectFilter::make('assigned_user_id')
                    ->label('Zuständig')
                    ->relationship('assignedUser', 'name'),
                SelectFilter::make('company')
                    ->relationship('client.company', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('close_reason')
                    ->options(CloseReason::class),
            ])
            ->recordActions([
                ViewAction::make(),
                ChangeStageAction::make(),
            ]);
    }
}
