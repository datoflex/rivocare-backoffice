<?php

namespace App\Filament\Resources\BillingItems\Pages;

use App\Filament\Resources\BillingItems\BillingItemResource;
use Filament\Resources\Pages\EditRecord;

class EditBillingItem extends EditRecord
{
    protected static string $resource = BillingItemResource::class;
}
