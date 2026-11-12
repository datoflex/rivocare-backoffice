<?php

namespace App\Filament\Resources\TaskTemplates;

use App\Enums\PlacementStage;
use App\Enums\Responsible;
use App\Filament\Resources\TaskTemplates\Pages\ManageTaskTemplates;
use App\Models\TaskTemplate;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use UnitEnum;

/**
 * The checklist created automatically when a case enters a stage.
 */
class TaskTemplateResource extends Resource
{
    protected static ?string $model = TaskTemplate::class;

    protected static ?string $modelLabel = 'Checklisten-Aufgabe';

    protected static ?string $pluralModelLabel = 'Phasen-Checklisten';

    protected static ?string $navigationLabel = 'Phasen-Checklisten';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Verwaltung';

    protected static ?int $navigationSort = 21;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('stage')
                    ->label('Wird angelegt, wenn der Fall diese Phase erreicht')
                    ->options(PlacementStage::class)
                    ->required(),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('responsible')
                    ->options(Responsible::class)
                    ->default(Responsible::Us)
                    ->required(),
                TextInput::make('due_in_days')
                    ->label('Fällig nach (Tagen)')
                    ->numeric()
                    ->minValue(0),
                TextInput::make('sort')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Toggle::make('is_active')
                    ->label('Aktiv')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultGroup(Group::make('stage')->label('Phase')->getTitleFromRecordUsing(fn (TaskTemplate $record): string => $record->stage->getLabel()))
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('title')
                    ->description(fn (TaskTemplate $record): ?string => $record->description)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('responsible')
                    ->badge(),
                TextColumn::make('due_in_days')
                    ->label('Fällig nach')
                    ->suffix(' Tage')
                    ->placeholder('-'),
                TextColumn::make('sort')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktiv')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('stage')
                    ->options(PlacementStage::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTaskTemplates::route('/'),
        ];
    }
}
