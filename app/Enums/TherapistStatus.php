<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TherapistStatus: string implements HasColor, HasLabel
{
    case Active = 'active';
    case Paused = 'paused';
    case Inactive = 'inactive';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Aktiv',
            self::Paused => 'Pausiert',
            self::Inactive => 'Inaktiv',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Paused => 'warning',
            self::Inactive => 'gray',
        };
    }
}
