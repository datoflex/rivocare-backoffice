<?php

namespace App\Filament\Resources\Clients\Tables;

use App\Models\Client;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('last_name')
                    ->label('Name')
                    ->formatStateUsing(fn (Client $record): string => $record->full_name)
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                TextColumn::make('company.name')
                    ->label('Unternehmen')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('placements_count')
                    ->label('Fälle')
                    ->counts('placements'),
                IconColumn::make('consent')
                    ->state(fn (Client $record): bool => $record->hasValidConsent())
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Angelegt')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('company')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
