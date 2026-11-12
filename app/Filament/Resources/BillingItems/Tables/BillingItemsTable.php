<?php

namespace App\Filament\Resources\BillingItems\Tables;

use App\Enums\BillingStatus;
use App\Enums\BillingType;
use App\Filament\Resources\Placements\PlacementResource;
use App\Models\BillingItem;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BillingItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['placement.client', 'company', 'therapist']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('placement.reference')
                    ->label('Fall')
                    ->url(fn (BillingItem $record): string => PlacementResource::getUrl('view', ['record' => $record->placement_id]))
                    ->color('primary')
                    ->searchable(),
                ...static::amountColumns(),
                TextColumn::make('invoiced_on')
                    ->date()
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('paid_on')
                    ->date()
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Erstellt')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(BillingStatus::class)
                    ->multiple(),
                SelectFilter::make('type')
                    ->options(BillingType::class),
                SelectFilter::make('company')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('markInvoiced')
                    ->label(fn (BillingItem $record): string => $record->isReceivable() ? 'Abgerechnet' : 'Rechnung erhalten')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->visible(fn (BillingItem $record): bool => $record->status === BillingStatus::Open)
                    ->schema(fn (BillingItem $record): array => [
                        TextInput::make('invoice_number')
                            ->required(),
                        DatePicker::make('invoiced_on')
                            ->default(today())
                            ->required(),
                    ])
                    ->action(fn (BillingItem $record, array $data) => $record->update([...$data, 'status' => BillingStatus::Invoiced])),
                Action::make('markPaid')
                    ->label('Bezahlt')
                    ->icon(Heroicon::OutlinedCheck)
                    ->color('success')
                    ->visible(fn (BillingItem $record): bool => $record->status === BillingStatus::Invoiced)
                    ->requiresConfirmation()
                    ->action(fn (BillingItem $record) => $record->update(['status' => BillingStatus::Paid, 'paid_on' => today()])),
                EditAction::make(),
            ]);
    }

    /**
     * @return array<TextColumn>
     */
    public static function amountColumns(): array
    {
        return [
            TextColumn::make('type')
                ->badge(),
            TextColumn::make('party')
                ->label('Unternehmen / Therapeut:in')
                ->state(fn (BillingItem $record): ?string => $record->isReceivable()
                    ? $record->company?->name
                    : $record->therapist?->full_name)
                ->description(fn (BillingItem $record): string => $record->isReceivable() ? 'zahlt an uns' : 'wir zahlen'),
            TextColumn::make('amount')
                ->money('EUR')
                ->placeholder('Preis fehlt')
                ->color(fn (BillingItem $record): ?string => $record->isReceivable() ? null : 'danger')
                ->summarize(Sum::make()->money('EUR')->label('Summe')),
            TextColumn::make('status')
                ->badge(),
            TextColumn::make('invoice_number')
                ->placeholder('-'),
        ];
    }
}
