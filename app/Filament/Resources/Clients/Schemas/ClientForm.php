<?php

namespace App\Filament\Resources\Clients\Schemas;

use App\Enums\ContactChannel;
use App\Enums\Gender;
use App\Enums\InsuranceType;
use App\Enums\SessionSetting;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientForm
{
    public const LANGUAGE_SUGGESTIONS = ['Deutsch', 'Englisch', 'Türkisch', 'Arabisch', 'Russisch', 'Polnisch', 'Spanisch', 'Französisch', 'Italienisch', 'Ukrainisch'];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Klient:in')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Unternehmen')
                            ->relationship('company', 'name')
                            ->default(fn (): ?string => request()->query('company'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email(),
                        TextInput::make('phone')
                            ->tel(),
                        Select::make('preferred_contact')
                            ->label('Bevorzugter Kontaktweg')
                            ->options(ContactChannel::class)
                            ->default(ContactChannel::Email)
                            ->required(),
                    ]),
                Section::make('Präferenzen für das Matching')
                    ->description('Nur praktische Kriterien. Niemals Gesundheits- oder Themeninformationen erfassen.')
                    ->columns(2)
                    ->schema([
                        TagsInput::make('languages')
                            ->suggestions(self::LANGUAGE_SUGGESTIONS),
                        Select::make('insurance_type')
                            ->options(InsuranceType::class),
                        Select::make('preferred_therapist_gender')
                            ->label('Bevorzugtes Geschlecht der Therapeut:in')
                            ->options(Gender::class)
                            ->placeholder('Keine Präferenz'),
                        Select::make('session_setting')
                            ->label('Setting')
                            ->options(SessionSetting::class)
                            ->default(SessionSetting::Either)
                            ->required(),
                        TextInput::make('postal_code')
                            ->maxLength(10),
                        TextInput::make('city'),
                    ]),
                Section::make('Einwilligung')
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('consent_given_at')
                            ->label('Einwilligung erteilt')
                            ->seconds(false),
                        DateTimePicker::make('consent_withdrawn_at')
                            ->label('Einwilligung widerrufen')
                            ->seconds(false)
                            ->afterOrEqual('consent_given_at'),
                    ]),
                Textarea::make('notes')
                    ->helperText('Nur organisatorische Informationen.')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
