<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompanyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Unternehmen')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('status')
                            ->badge(),
                        TextEntry::make('website')
                            ->url(fn (?string $state): ?string => $state)
                            ->openUrlInNewTab()
                            ->placeholder('-'),
                        TextEntry::make('email')
                            ->copyable()
                            ->placeholder('-'),
                        TextEntry::make('phone')
                            ->copyable()
                            ->placeholder('-'),
                        TextEntry::make('billing_email')
                            ->label('E-Mail für Rechnungen')
                            ->placeholder('-'),
                        TextEntry::make('address')
                            ->state(fn ($record): ?string => collect([$record->street, trim("{$record->postal_code} {$record->city}")])->filter()->implode(', ') ?: null)
                            ->placeholder('-'),
                    ]),
                Section::make('Vertrag & Preise')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('contract_starts_on')
                            ->label('Vertragsbeginn')
                            ->date()
                            ->placeholder('-'),
                        TextEntry::make('contract_ends_on')
                            ->label('Vertragsende')
                            ->date()
                            ->placeholder('-'),
                        TextEntry::make('first_session_price')
                            ->label('Preis pro erster Sitzung')
                            ->money('EUR')
                            ->placeholder('-'),
                        TextEntry::make('success_fee')
                            ->money('EUR')
                            ->placeholder('-'),
                    ])
                    ->visible(fn (): bool => auth()->user()->isAdmin()),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }
}
