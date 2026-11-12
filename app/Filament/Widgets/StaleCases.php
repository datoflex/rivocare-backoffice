<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Placements\PlacementResource;
use App\Models\Placement;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * Open cases that haven't changed for a while and may have been forgotten.
 */
class StaleCases extends TableWidget
{
    public const STALE_AFTER_DAYS = 14;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Fälle ohne Änderung seit '.self::STALE_AFTER_DAYS.' Tagen';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Placement::query()
                ->with(['client.company', 'assignedUser'])
                ->open()
                ->where('updated_at', '<', now()->subDays(self::STALE_AFTER_DAYS))
                ->oldest('updated_at'))
            ->paginated([5, 10])
            ->emptyStateHeading('Keine liegengebliebenen Fälle')
            ->recordUrl(fn (Placement $record): string => PlacementResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('reference'),
                TextColumn::make('client.full_name')
                    ->label('Klient:in'),
                TextColumn::make('client.company.name')
                    ->label('Unternehmen'),
                TextColumn::make('stage')
                    ->badge(),
                TextColumn::make('assignedUser.name')
                    ->label('Zuständig')
                    ->placeholder('-'),
                TextColumn::make('updated_at')
                    ->label('Letzte Änderung')
                    ->since(),
            ]);
    }
}
