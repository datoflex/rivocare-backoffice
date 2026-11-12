<?php

namespace App\Filament\Resources\Therapists;

use App\Filament\RelationManagers\DocumentsRelationManager;
use App\Filament\RelationManagers\NotesRelationManager;
use App\Filament\RelationManagers\TasksRelationManager;
use App\Filament\Resources\Therapists\Pages\CreateTherapist;
use App\Filament\Resources\Therapists\Pages\EditTherapist;
use App\Filament\Resources\Therapists\Pages\ListTherapists;
use App\Filament\Resources\Therapists\Pages\ViewTherapist;
use App\Filament\Resources\Therapists\Schemas\TherapistForm;
use App\Filament\Resources\Therapists\Schemas\TherapistInfolist;
use App\Filament\Resources\Therapists\Tables\TherapistsTable;
use App\Models\Therapist;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class TherapistResource extends Resource
{
    protected static ?string $model = Therapist::class;

    protected static ?string $modelLabel = 'Therapeut:in';

    protected static ?string $pluralModelLabel = 'Therapeut:innen';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static string|UnitEnum|null $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 12;

    protected static ?string $recordTitleAttribute = 'last_name';

    public static function getRecordTitle(?Model $record): string
    {
        return $record?->full_name ?? static::getModelLabel();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['first_name', 'last_name', 'practice_name', 'city'];
    }

    public static function form(Schema $schema): Schema
    {
        return TherapistForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TherapistInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TherapistsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            TasksRelationManager::class,
            NotesRelationManager::class,
            DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTherapists::route('/'),
            'create' => CreateTherapist::route('/create'),
            'view' => ViewTherapist::route('/{record}'),
            'edit' => EditTherapist::route('/{record}/edit'),
        ];
    }
}
