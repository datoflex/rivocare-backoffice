<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use App\Filament\Resources\Clients\ClientResource;
use App\Models\Client;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClientsRelationManager extends RelationManager
{
    protected static string $relationship = 'clients';

    protected static ?string $title = 'Klient:innen';

    protected static string|\BackedEnum|null $icon = Heroicon::OutlinedUser;

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('last_name')
            ->recordUrl(fn (Client $record): string => ClientResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('last_name')
                    ->label('Name')
                    ->formatStateUsing(fn (Client $record): string => $record->full_name)
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('placements_count')
                    ->label('Fälle')
                    ->counts('placements'),
                TextColumn::make('created_at')
                    ->label('Angelegt')
                    ->date(),
            ])
            ->headerActions([
                Action::make('create')
                    ->label('Neue:r Klient:in')
                    ->icon(Heroicon::OutlinedPlus)
                    ->url(fn (): string => ClientResource::getUrl('create', ['company' => $this->getOwnerRecord()->getKey()])),
            ]);
    }
}
