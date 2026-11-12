<?php

namespace App\Filament\Resources\BillingItems;

use App\Filament\Resources\BillingItems\Pages\EditBillingItem;
use App\Filament\Resources\BillingItems\Pages\ListBillingItems;
use App\Filament\Resources\BillingItems\Schemas\BillingItemForm;
use App\Filament\Resources\BillingItems\Tables\BillingItemsTable;
use App\Models\BillingItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Billing items are created by the case workflow, never by hand.
 */
class BillingItemResource extends Resource
{
    protected static ?string $model = BillingItem::class;

    protected static ?string $pluralModelLabel = 'Abrechnungsposten';

    protected static ?string $navigationLabel = 'Abrechnung';

    protected static ?string $modelLabel = 'Abrechnungsposten';

    protected static ?string $slug = 'billing';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Verwaltung';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return BillingItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BillingItemsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBillingItems::route('/'),
            'edit' => EditBillingItem::route('/{record}/edit'),
        ];
    }
}
