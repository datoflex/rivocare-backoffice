<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContactsRelationManager extends RelationManager
{
    protected static string $relationship = 'contacts';

    protected static ?string $modelLabel = 'Ansprechperson';

    protected static ?string $title = 'Ansprechpersonen';

    protected static string|\BackedEnum|null $icon = Heroicon::OutlinedUserGroup;

    /**
     * Editable on the record's view page too, where the team works.
     */
    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('role')
                    ->placeholder('z. B. HR, Benefits'),
                TextInput::make('email')
                    ->email(),
                TextInput::make('phone')
                    ->tel(),
                Toggle::make('is_primary')
                    ->label('Hauptansprechperson'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('role')
                    ->placeholder('-'),
                TextColumn::make('email')
                    ->copyable()
                    ->placeholder('-'),
                TextColumn::make('phone')
                    ->copyable()
                    ->placeholder('-'),
                IconColumn::make('is_primary')
                    ->label('Haupt')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
