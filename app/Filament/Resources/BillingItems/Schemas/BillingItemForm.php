<?php

namespace App\Filament\Resources\BillingItems\Schemas;

use App\Enums\BillingStatus;
use App\Enums\BillingType;
use App\Models\BillingItem;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BillingItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->options(BillingType::class)
                    ->disabled(),
                TextInput::make('amount')
                    ->numeric()
                    ->minValue(0)
                    ->prefix('€')
                    ->required(),
                Select::make('status')
                    ->options(BillingStatus::class)
                    ->required(),
                TextInput::make('invoice_number')
                    ->label(fn (?BillingItem $record): string => $record?->isReceivable() === false ? 'Rechnungsnummer Therapeut:in' : 'Unsere Rechnungsnummer'),
                DatePicker::make('invoiced_on')
                    ->label(fn (?BillingItem $record): string => $record?->isReceivable() === false ? 'Rechnung erhalten' : 'Abgerechnet am'),
                DatePicker::make('paid_on')
                    ->label('Bezahlt am'),
                TextInput::make('note')
                    ->columnSpanFull(),
            ]);
    }
}
