<?php

namespace App\Filament\Resources\Therapists\Pages;

use App\Filament\Resources\Therapists\TherapistResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTherapist extends ViewRecord
{
    protected static string $resource = TherapistResource::class;

    public function getTitle(): string
    {
        return static::getResource()::getRecordTitle($this->getRecord());
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
