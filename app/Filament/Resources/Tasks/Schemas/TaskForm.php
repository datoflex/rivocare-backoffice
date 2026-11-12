<?php

namespace App\Filament\Resources\Tasks\Schemas;

use App\Enums\Responsible;
use App\Enums\TaskStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(static::fields());
    }

    /**
     * @return array<Component>
     */
    public static function fields(): array
    {
        return [
            TextInput::make('title')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            Textarea::make('description')
                ->rows(3)
                ->columnSpanFull(),
            Select::make('responsible')
                ->options(Responsible::class)
                ->helperText('Wer handeln muss. „Wartet“ nutzen, solange es bei jemand anderem liegt.')
                ->required(),
            Select::make('status')
                ->options(TaskStatus::class)
                ->required(),
            Select::make('assigned_user_id')
                ->label('Zuständig')
                ->relationship('assignedUser', 'name')
                ->helperText('Wer im Team nachfasst.'),
            DatePicker::make('due_on')
                ->label('Fällig'),
        ];
    }
}
