<?php

namespace App\Filament\Resources\Placements\Pages;

use App\Filament\Actions\ChangeStageAction;
use App\Filament\Resources\Placements\PlacementResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPlacement extends ViewRecord
{
    protected static string $resource = PlacementResource::class;

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
            // Reloads the page so the new stage's checklist shows in the tasks tab.
            ChangeStageAction::make()
                ->after(fn () => $this->redirect(PlacementResource::getUrl('view', ['record' => $this->record]))),
            EditAction::make(),
        ];
    }
}
