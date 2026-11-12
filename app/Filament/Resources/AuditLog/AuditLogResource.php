<?php

namespace App\Filament\Resources\AuditLog;

use App\Filament\Resources\AuditLog\Pages\ListAuditLog;
use App\Models\User;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;
use UnitEnum;

/**
 * Read-only view of who changed or viewed what.
 */
class AuditLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $pluralModelLabel = 'Protokolleinträge';

    protected static ?string $navigationLabel = 'Protokoll';

    protected static ?string $modelLabel = 'Protokolleintrag';

    protected static ?string $slug = 'audit-log';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Verwaltung';

    protected static ?int $navigationSort = 23;

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('created_at')
                    ->label('Wann')
                    ->dateTime(),
                TextEntry::make('causer.name')
                    ->label('Nutzer:in')
                    ->placeholder('System'),
                TextEntry::make('event'),
                TextEntry::make('subject_type')
                    ->label('Datensatz')
                    ->formatStateUsing(fn (Activity $record): string => static::recordLabel($record)),
                KeyValueEntry::make('properties.old')
                    ->label('Vorher')
                    ->columnSpanFull(),
                KeyValueEntry::make('properties.attributes')
                    ->label('Nachher')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Wann')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('causer.name')
                    ->label('Nutzer:in')
                    ->placeholder('System'),
                TextColumn::make('event')
                    ->badge(),
                TextColumn::make('subject_type')
                    ->label('Datensatz')
                    ->formatStateUsing(fn (Activity $record): string => static::recordLabel($record)),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->options([
                        'created' => 'Erstellt',
                        'updated' => 'Geändert',
                        'deleted' => 'Gelöscht',
                        'viewed' => 'Angesehen',
                    ]),
                SelectFilter::make('causer_id')
                    ->label('Nutzer:in')
                    ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'id')->all()),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function recordLabel(Activity $activity): string
    {
        $type = match (class_basename($activity->subject_type)) {
            'Placement' => 'Fall',
            'Client' => 'Klient:in',
            'Company' => 'Unternehmen',
            'CompanyContact' => 'Ansprechperson',
            'Therapist' => 'Therapeut:in',
            'PlacementCandidate' => 'Kandidat:in',
            'Task' => 'Aufgabe',
            'TaskTemplate' => 'Checklisten-Aufgabe',
            'Document' => 'Dokument',
            'BillingItem' => 'Abrechnungsposten',
            'User' => 'Nutzer:in',
            default => class_basename($activity->subject_type),
        };

        return "{$type} #{$activity->subject_id}";
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuditLog::route('/'),
        ];
    }
}
