<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Capacity: string implements HasColor, HasLabel
{
    case Available = 'available';
    case Limited = 'limited';
    case Full = 'full';

    public function getLabel(): string
    {
        return match ($this) {
            self::Available => 'Verfügbar',
            self::Limited => 'Eingeschränkt',
            self::Full => 'Voll',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Available => 'success',
            self::Limited => 'warning',
            self::Full => 'danger',
        };
    }
}
