<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum NoteType: string implements HasColor, HasLabel
{
    case Note = 'note';
    case Call = 'call';
    case Email = 'email';
    case System = 'system';

    public function getLabel(): string
    {
        return match ($this) {
            self::Note => 'Notiz',
            self::Call => 'Anruf',
            self::Email => 'E-Mail',
            self::System => 'System',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Note => 'gray',
            self::Call => 'info',
            self::Email => 'primary',
            self::System => 'gray',
        };
    }
}
