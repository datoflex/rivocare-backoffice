<?php

namespace App\Filament\Resources\Clients\Schemas;

use App\Filament\Resources\Companies\CompanyResource;
use App\Models\Client;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Klient:in')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('full_name')
                            ->label('Name'),
                        TextEntry::make('company.name')
                            ->label('Unternehmen')
                            ->url(fn (Client $record): string => CompanyResource::getUrl('view', ['record' => $record->company_id]))
                            ->color('primary'),
                        TextEntry::make('preferred_contact')
                            ->label('Bevorzugter Kanal'),
                        TextEntry::make('email')
                            ->copyable()
                            ->placeholder('-'),
                        TextEntry::make('phone')
                            ->copyable()
                            ->placeholder('-'),
                        TextEntry::make('consent')
                            ->state(fn (Client $record): string => match (true) {
                                $record->consent_withdrawn_at !== null => 'Widerrufen am '.$record->consent_withdrawn_at->format('d.m.Y'),
                                $record->consent_given_at !== null => 'Erteilt am '.$record->consent_given_at->format('d.m.Y'),
                                default => 'Fehlt',
                            })
                            ->badge()
                            ->color(fn (Client $record): string => $record->hasValidConsent() ? 'success' : 'danger'),
                    ]),
                Section::make('Präferenzen für das Matching')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('languages')
                            ->badge()
                            ->placeholder('-'),
                        TextEntry::make('insurance_type')
                            ->badge()
                            ->placeholder('-'),
                        TextEntry::make('preferred_therapist_gender')
                            ->label('Geschlecht Therapeut:in')
                            ->placeholder('Keine Präferenz'),
                        TextEntry::make('session_setting')
                            ->label('Setting'),
                        TextEntry::make('location')
                            ->state(fn (Client $record): ?string => trim("{$record->postal_code} {$record->city}") ?: null)
                            ->placeholder('-'),
                    ]),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }
}
