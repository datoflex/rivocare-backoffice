<?php

namespace App\Filament\Resources\Placements\Schemas;

use App\Models\Client;
use App\Models\CompanyContact;
use App\Models\Therapist;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

/**
 * The stage is not edited here; it changes on the pipeline board or
 * through the "Change stage" action, which enforce the stage rules.
 */
class PlacementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Fall')
                    ->columns(2)
                    ->schema([
                        Select::make('client_id')
                            ->label('Klient:in')
                            ->relationship('client', 'last_name')
                            ->getOptionLabelFromRecordUsing(fn (Client $record): string => "{$record->full_name} ({$record->company->name})")
                            ->searchable(['first_name', 'last_name'])
                            ->default(fn (): ?string => request()->query('client'))
                            ->required()
                            ->live()
                            ->disabledOn('edit'),
                        Select::make('company_contact_id')
                            ->label('Vermittelt durch')
                            ->options(fn (Get $get): array => CompanyContact::query()
                                ->whereHas('company.clients', fn ($query) => $query->whereKey($get('client_id')))
                                ->pluck('name', 'id')
                                ->all())
                            ->placeholder('Ansprechperson im Unternehmen, die vermittelt hat'),
                        Select::make('assigned_user_id')
                            ->label('Zuständig')
                            ->relationship('assignedUser', 'name')
                            ->default(fn (): ?int => auth()->id()),
                        Select::make('therapist_id')
                            ->label('Therapeut:in')
                            ->relationship('therapist', 'last_name')
                            ->getOptionLabelFromRecordUsing(fn (Therapist $record): string => $record->full_name)
                            ->searchable(['first_name', 'last_name'])
                            ->hiddenOn('create'),
                        DateTimePicker::make('first_session_at')
                            ->label('Erste Sitzung')
                            ->seconds(false)
                            ->hiddenOn('create'),
                    ]),
            ])
            ->columns(1);
    }
}
