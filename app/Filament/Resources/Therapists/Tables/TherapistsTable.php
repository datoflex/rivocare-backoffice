<?php

namespace App\Filament\Resources\Therapists\Tables;

use App\Enums\Capacity;
use App\Enums\Gender;
use App\Enums\InsuranceType;
use App\Enums\TherapistStatus;
use App\Enums\TherapyApproach;
use App\Models\Therapist;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TherapistsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('last_name')
            ->columns([
                TextColumn::make('last_name')
                    ->label('Name')
                    ->formatStateUsing(fn (Therapist $record): string => $record->full_name)
                    ->description(fn (Therapist $record): ?string => $record->practice_name)
                    ->searchable(['first_name', 'last_name', 'practice_name'])
                    ->sortable(),
                TextColumn::make('city')
                    ->description(fn (Therapist $record): ?string => $record->postal_code)
                    ->searchable(['city', 'postal_code'])
                    ->sortable()
                    ->placeholder('-'),
                IconColumn::make('offers_online')
                    ->label('Online')
                    ->boolean(),
                TextColumn::make('languages')
                    ->badge()
                    ->placeholder('-'),
                TextColumn::make('capacity')
                    ->badge()
                    ->tooltip(fn (Therapist $record): ?string => $record->capacity_note),
                TextColumn::make('status')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(TherapistStatus::class)
                    ->default(TherapistStatus::Active->value),
                SelectFilter::make('capacity')
                    ->options(Capacity::class)
                    ->multiple(),
                SelectFilter::make('gender')
                    ->options(Gender::class),
                TernaryFilter::make('offers_online')
                    ->label('Online'),
                SelectFilter::make('insurance_type')
                    ->label('Akzeptiert')
                    ->options(InsuranceType::class)
                    ->query(fn (Builder $query, array $data) => $query->when(
                        $data['value'],
                        fn (Builder $query, string $value) => $query->whereJsonContains('insurance_types', $value),
                    )),
                SelectFilter::make('approach')
                    ->options(TherapyApproach::class)
                    ->query(fn (Builder $query, array $data) => $query->when(
                        $data['value'],
                        fn (Builder $query, string $value) => $query->whereJsonContains('approaches', $value),
                    )),
                Filter::make('language')
                    ->schema([
                        TextInput::make('language'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query->when(
                        $data['language'],
                        fn (Builder $query, string $language) => $query->whereJsonContains('languages', $language),
                    )),
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
