<?php

namespace App\Filament\Resources\Placements;

use App\Filament\RelationManagers\DocumentsRelationManager;
use App\Filament\RelationManagers\NotesRelationManager;
use App\Filament\RelationManagers\TasksRelationManager;
use App\Filament\Resources\Placements\Pages\CreatePlacement;
use App\Filament\Resources\Placements\Pages\EditPlacement;
use App\Filament\Resources\Placements\Pages\ListPlacements;
use App\Filament\Resources\Placements\Pages\ViewPlacement;
use App\Filament\Resources\Placements\RelationManagers\BillingItemsRelationManager;
use App\Filament\Resources\Placements\RelationManagers\CandidatesRelationManager;
use App\Filament\Resources\Placements\Schemas\PlacementForm;
use App\Filament\Resources\Placements\Schemas\PlacementInfolist;
use App\Filament\Resources\Placements\Tables\PlacementsTable;
use App\Models\Placement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * A "case" in the UI. The model is called Placement because `case`
 * is a reserved word in PHP.
 */
class PlacementResource extends Resource
{
    protected static ?string $model = Placement::class;

    protected static ?string $modelLabel = 'Fall';

    protected static ?string $pluralModelLabel = 'Fälle';

    protected static ?string $slug = 'cases';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'reference';

    public static function getRecordTitle(?Model $record): string
    {
        return $record ? "{$record->reference} · {$record->client->full_name}" : static::getModelLabel();
    }

    public static function form(Schema $schema): Schema
    {
        return PlacementForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PlacementInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PlacementsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            TasksRelationManager::class,
            CandidatesRelationManager::class,
            NotesRelationManager::class,
            DocumentsRelationManager::class,
            BillingItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlacements::route('/'),
            'create' => CreatePlacement::route('/create'),
            'view' => ViewPlacement::route('/{record}'),
            'edit' => EditPlacement::route('/{record}/edit'),
        ];
    }
}
