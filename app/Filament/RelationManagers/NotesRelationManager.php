<?php

namespace App\Filament\RelationManagers;

use App\Enums\NoteType;
use App\Models\Note;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Timeline of notes, calls, emails and system events.
 */
class NotesRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';

    protected static ?string $modelLabel = 'Eintrag';

    protected static ?string $title = 'Verlauf';

    protected static string|\BackedEnum|null $icon = Heroicon::OutlinedChatBubbleLeftEllipsis;

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
                Select::make('type')
                    ->options(collect(NoteType::cases())
                        ->reject(fn (NoteType $type): bool => $type === NoteType::System)
                        ->mapWithKeys(fn (NoteType $type): array => [$type->value => $type->getLabel()]))
                    ->default(NoteType::Note->value)
                    ->required(),
                DateTimePicker::make('occurred_at')
                    ->label('Wann')
                    ->seconds(false)
                    ->default(now())
                    ->required(),
                Textarea::make('body')
                    ->label('Text')
                    ->helperText('Nur organisatorische Informationen. Keine Gesundheits- oder Themendetails.')
                    ->rows(4)
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('occurred_at', 'desc')
            ->columns([
                TextColumn::make('occurred_at')
                    ->label('Wann')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('body')
                    ->label('Text')
                    ->wrap(),
                TextColumn::make('user.name')
                    ->label('Von')
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(NoteType::class),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Eintrag hinzufügen')
                    ->mutateDataUsing(fn (array $data): array => [...$data, 'user_id' => auth()->id()]),
            ])
            ->recordActions([
                EditAction::make()
                    ->hidden(fn (Note $record): bool => $record->type === NoteType::System),
                DeleteAction::make()
                    ->hidden(fn (Note $record): bool => $record->type === NoteType::System),
            ]);
    }
}
