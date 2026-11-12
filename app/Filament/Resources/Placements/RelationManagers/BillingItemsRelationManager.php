<?php

namespace App\Filament\Resources\Placements\RelationManagers;

use App\Filament\Resources\BillingItems\Schemas\BillingItemForm;
use App\Filament\Resources\BillingItems\Tables\BillingItemsTable;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BillingItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'billingItems';

    protected static ?string $modelLabel = 'Abrechnungsposten';

    protected static ?string $title = 'Abrechnung';

    protected static string|\BackedEnum|null $icon = Heroicon::OutlinedBanknotes;

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()->isAdmin();
    }

    /**
     * Editable on the record's view page too, where the team works.
     */
    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return BillingItemForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ...BillingItemsTable::amountColumns(),
                TextColumn::make('created_at')
                    ->label('Erstellt')
                    ->date(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
