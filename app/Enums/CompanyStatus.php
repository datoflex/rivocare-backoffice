<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CompanyStatus: string implements HasColor, HasLabel
{
    case Lead = 'lead';
    case Active = 'active';
    case Paused = 'paused';
    case Ended = 'ended';

    public function getLabel(): string
    {
        return match ($this) {
            self::Lead => 'Interessent',
            self::Active => 'Aktiv',
            self::Paused => 'Pausiert',
            self::Ended => 'Beendet',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Lead => 'gray',
            self::Active => 'success',
            self::Paused => 'warning',
            self::Ended => 'danger',
        };
    }
}
