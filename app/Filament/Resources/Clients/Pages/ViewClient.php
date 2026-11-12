<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\Placements\PlacementResource;
use App\Models\Client;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    public function getTitle(): string
    {
        return static::getResource()::getRecordTitle($this->getRecord());
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        activity()->performedOn($this->record)->event('viewed')->log('viewed');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('openCase')
                ->label('Neuen Fall eröffnen')
                ->icon(Heroicon::OutlinedPlus)
                ->requiresConfirmation()
                ->modalDescription('Legt einen neuen Fall in der Phase „Neu“ samt Checkliste an.')
                ->action(function (Client $record): void {
                    $placement = $record->placements()->create();

                    $this->redirect(PlacementResource::getUrl('view', ['record' => $placement]));
                }),
            EditAction::make(),
        ];
    }
}
