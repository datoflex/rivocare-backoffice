<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use App\Filament\Resources\Placements\PlacementResource;
use App\Models\Placement;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlacementsRelationManager extends RelationManager
{
    protected static string $relationship = 'placements';

    protected static ?string $title = 'Fälle';

    protected static string|\BackedEnum|null $icon = Heroicon::OutlinedBriefcase;

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reference')
            ->recordUrl(fn (Placement $record): string => PlacementResource::getUrl('view', ['record' => $record]))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference'),
                TextColumn::make('stage')
                    ->badge(),
                TextColumn::make('therapist.full_name')
                    ->label('Therapeut:in')
                    ->placeholder('-'),
                TextColumn::make('close_reason')
                    ->badge()
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Eröffnet')
                    ->date(),
            ]);
    }
}
