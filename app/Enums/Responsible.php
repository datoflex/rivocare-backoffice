<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Responsible: string implements HasColor, HasLabel
{
    case Us = 'us';
    case Client = 'client';
    case Therapist = 'therapist';
    case Company = 'company';

    public function getLabel(): string
    {
        return match ($this) {
            self::Us => 'Wir',
            self::Client => 'Klient:in',
            self::Therapist => 'Therapeut:in',
            self::Company => 'Unternehmen',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Us => 'primary',
            self::Client => 'warning',
            self::Therapist => 'info',
            self::Company => 'gray',
        };
    }
}
