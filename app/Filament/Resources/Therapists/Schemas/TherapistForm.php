<?php

namespace App\Filament\Resources\Therapists\Schemas;

use App\Enums\Capacity;
use App\Enums\Gender;
use App\Enums\InsuranceType;
use App\Enums\TherapistStatus;
use App\Enums\TherapyApproach;
use App\Filament\Resources\Clients\Schemas\ClientForm;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TherapistForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Therapeut:in')
                    ->columns(3)
                    ->schema([
                        TextInput::make('title')
                            ->placeholder('e.g. Dr., Dipl.-Psych.'),
                        TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->required()
                            ->maxLength(255),
                        Select::make('gender')
                            ->options(Gender::class),
                        TextInput::make('email')
                            ->email(),
                        TextInput::make('phone')
                            ->tel(),
                    ]),
                Section::make('Praxis')
                    ->columns(3)
                    ->schema([
                        TextInput::make('practice_name')
                            ->columnSpanFull(),
                        TextInput::make('street'),
                        TextInput::make('postal_code')
                            ->maxLength(10),
                        TextInput::make('city'),
                        Toggle::make('offers_in_person')
                            ->label('Bietet Sitzungen vor Ort an')
                            ->default(true),
                        Toggle::make('offers_online')
                            ->label('Bietet Online-Sitzungen an'),
                    ]),
                Section::make('Profil')
                    ->columns(2)
                    ->schema([
                        CheckboxList::make('approaches')
                            ->options(TherapyApproach::class),
                        CheckboxList::make('insurance_types')
                            ->label('Akzeptiert')
                            ->options(InsuranceType::class),
                        TagsInput::make('languages')
                            ->suggestions(ClientForm::LANGUAGE_SUGGESTIONS),
                        TagsInput::make('focus_areas')
                            ->label('Schwerpunkte')
                            ->placeholder('z. B. Angst, Burnout, Paare'),
                    ]),
                Section::make('Verfügbarkeit')
                    ->columns(3)
                    ->schema([
                        Select::make('status')
                            ->options(TherapistStatus::class)
                            ->default(TherapistStatus::Active)
                            ->required(),
                        Select::make('capacity')
                            ->options(Capacity::class)
                            ->default(Capacity::Available)
                            ->required(),
                        TextInput::make('capacity_note')
                            ->placeholder('z. B. neue Klient:innen ab Januar'),
                    ]),
                Section::make('Vereinbarung')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('agreement_signed_on')
                            ->label('Vereinbarung unterschrieben'),
                        TextInput::make('first_session_fee')
                            ->label('Unser Honorar pro erster Sitzung')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('€')
                            ->visible(fn (): bool => auth()->user()->isAdmin()),
                    ]),
                Textarea::make('notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
