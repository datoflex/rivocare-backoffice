<?php

namespace App\Filament\Resources\Placements\Schemas;

use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\Therapists\TherapistResource;
use App\Models\Placement;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PlacementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(4)
                    ->schema([
                        TextEntry::make('reference')
                            ->copyable()
                            ->helperText('Diese Referenz für alle Notizen außerhalb des Systems verwenden.'),
                        TextEntry::make('stage')
                            ->badge(),
                        TextEntry::make('close_reason')
                            ->badge()
                            ->visible(fn (Placement $record): bool => $record->closed_at !== null),
                        TextEntry::make('assignedUser.name')
                            ->label('Zuständig')
                            ->placeholder('-'),
                        TextEntry::make('client.full_name')
                            ->label('Klient:in')
                            ->url(fn (Placement $record): string => ClientResource::getUrl('view', ['record' => $record->client_id]))
                            ->color('primary'),
                        TextEntry::make('client.company.name')
                            ->label('Unternehmen')
                            ->url(fn (Placement $record): string => CompanyResource::getUrl('view', ['record' => $record->client->company_id]))
                            ->color('primary'),
                        TextEntry::make('companyContact.name')
                            ->label('Vermittelt durch')
                            ->placeholder('-'),
                        TextEntry::make('client.preferred_contact')
                            ->label('Kontakt per'),
                        TextEntry::make('therapist.full_name')
                            ->label('Therapeut:in')
                            ->url(fn (Placement $record): ?string => $record->therapist_id
                                ? TherapistResource::getUrl('view', ['record' => $record->therapist_id])
                                : null)
                            ->color('primary')
                            ->placeholder('Noch nicht zugeordnet'),
                        TextEntry::make('first_session_at')
                            ->label('Erste Sitzung')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('created_at')
                            ->label('Eröffnet')
                            ->dateTime(),
                        TextEntry::make('closed_at')
                            ->label('Abgeschlossen')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
            ])
            ->columns(1);
    }
}
