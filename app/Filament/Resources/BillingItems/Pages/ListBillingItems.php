<?php

namespace App\Filament\Resources\BillingItems\Pages;

use App\Enums\BillingStatus;
use App\Enums\BillingType;
use App\Filament\Resources\BillingItems\BillingItemResource;
use App\Models\BillingItem;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBillingItems extends ListRecords
{
    protected static string $resource = BillingItemResource::class;

    public function getTabs(): array
    {
        $receivable = [BillingType::FirstSession, BillingType::SuccessFee];

        return [
            'to_invoice' => Tab::make('Abzurechnen')
                ->badge(BillingItem::query()->whereIn('type', $receivable)->where('status', BillingStatus::Open)->count() ?: null)
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('type', $receivable)->where('status', BillingStatus::Open)),
            'to_pay' => Tab::make('Zu bezahlen')
                ->badge(BillingItem::query()->where('type', BillingType::TherapistFee)->where('status', '!=', BillingStatus::Paid)->count() ?: null)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', BillingType::TherapistFee)->where('status', '!=', BillingStatus::Paid)),
            'awaiting_payment' => Tab::make('Zahlung ausstehend')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('type', $receivable)->where('status', BillingStatus::Invoiced)),
            'all' => Tab::make('Alle'),
        ];
    }
}
