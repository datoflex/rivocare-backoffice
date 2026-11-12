<?php

namespace App\Filament\RelationManagers;

use App\Models\Document;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

/**
 * Files are stored on the private disk and only streamed to signed-in users.
 */
class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $modelLabel = 'Dokument';

    protected static ?string $title = 'Dokumente';

    protected static string|\BackedEnum|null $icon = Heroicon::OutlinedDocument;

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
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('path')
                    ->label('Datei')
                    ->disk(Document::DISK)
                    ->directory('documents')
                    ->visibility('private')
                    ->storeFileNamesIn('original_name')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'image/jpeg',
                        'image/png',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ])
                    ->maxSize(10 * 1024)
                    ->required(),
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('original_name')
                    ->label('Datei'),
                TextColumn::make('uploadedBy.name')
                    ->label('Hochgeladen von')
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Hochgeladen')
                    ->dateTime(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Hochladen')
                    ->mutateDataUsing(fn (array $data): array => [...$data, 'uploaded_by_id' => auth()->id()]),
            ])
            ->recordActions([
                Action::make('download')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->action(fn (Document $record) => Storage::disk(Document::DISK)
                        ->download($record->path, $record->original_name)),
                DeleteAction::make(),
            ]);
    }
}
