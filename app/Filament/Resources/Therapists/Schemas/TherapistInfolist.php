<?php

namespace App\Filament\Resources\Therapists\Schemas;

use App\Enums\InsuranceType;
use App\Enums\TherapyApproach;
use App\Models\Therapist;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TherapistInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Therapeut:in')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('full_name')
                            ->label('Name'),
                        TextEntry::make('email')
                            ->copyable()
                            ->placeholder('-'),
                        TextEntry::make('phone')
                            ->copyable()
                            ->placeholder('-'),
                        TextEntry::make('practice_name')
                            ->placeholder('-'),
                        TextEntry::make('address')
                            ->state(fn (Therapist $record): ?string => collect([$record->street, trim("{$record->postal_code} {$record->city}")])->filter()->implode(', ') ?: null)
                            ->placeholder('-'),
                        TextEntry::make('gender')
                            ->placeholder('-'),
                    ]),
                Section::make('Profil & Verfügbarkeit')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('status')
                            ->badge(),
                        TextEntry::make('capacity')
                            ->badge()
                            ->helperText(fn (Therapist $record): ?string => $record->capacity_note),
                        IconEntry::make('offers_online')
                            ->label('Online')
                            ->boolean(),
                        TextEntry::make('approaches')
                            ->formatStateUsing(fn (string $state): string => TherapyApproach::tryFrom($state)?->getLabel() ?? $state)
                            ->badge()
                            ->placeholder('-'),
                        TextEntry::make('insurance_types')
                            ->label('Akzeptiert')
                            ->formatStateUsing(fn (string $state): string => InsuranceType::tryFrom($state)?->getLabel() ?? $state)
                            ->badge()
                            ->placeholder('-'),
                        TextEntry::make('languages')
                            ->badge()
                            ->placeholder('-'),
                        TextEntry::make('focus_areas')
                            ->label('Schwerpunkte')
                            ->badge()
                            ->color('gray')
                            ->placeholder('-'),
                    ]),
                Section::make('Vereinbarung')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('agreement_signed_on')
                            ->label('Vereinbarung unterschrieben')
                            ->date()
                            ->placeholder('Nicht unterschrieben'),
                        TextEntry::make('first_session_fee')
                            ->label('Honorar pro erster Sitzung')
                            ->money('EUR')
                            ->placeholder('-')
                            ->visible(fn (): bool => auth()->user()->isAdmin()),
                    ]),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }
}
