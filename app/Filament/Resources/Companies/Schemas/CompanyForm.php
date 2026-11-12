<?php

namespace App\Filament\Resources\Companies\Schemas;

use App\Enums\CompanyStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Unternehmen')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Select::make('status')
                            ->options(CompanyStatus::class)
                            ->default(CompanyStatus::Lead)
                            ->required(),
                        TextInput::make('email')
                            ->email(),
                        TextInput::make('phone')
                            ->tel(),
                        TextInput::make('website')
                            ->url(),
                        TextInput::make('billing_email')
                            ->label('E-Mail für Rechnungen')
                            ->email(),
                        TextInput::make('street'),
                        TextInput::make('postal_code')
                            ->maxLength(10),
                        TextInput::make('city'),
                    ]),
                Section::make('Vertrag & Preise')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('contract_starts_on')
                            ->label('Vertragsbeginn'),
                        DatePicker::make('contract_ends_on')
                            ->label('Vertragsende')
                            ->afterOrEqual('contract_starts_on'),
                        TextInput::make('first_session_price')
                            ->label('Preis pro erster Sitzung')
                            ->helperText('Wird dem Unternehmen berechnet, sobald die erste Sitzung stattgefunden hat.')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('€'),
                        TextInput::make('success_fee')
                            ->label('Vermittlungsgebühr')
                            ->helperText('Wird berechnet, wenn Klient:in und Therapeut:in den Therapieeinstieg starten.')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('€'),
                    ])
                    ->visible(fn (): bool => auth()->user()->isAdmin()),
                Textarea::make('notes')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}
