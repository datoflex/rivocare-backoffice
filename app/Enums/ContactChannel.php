<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ContactChannel: string implements HasLabel
{
    case Email = 'email';
    case Phone = 'phone';

    public function getLabel(): string
    {
        return match ($this) {
            self::Email => 'E-Mail',
            self::Phone => 'Telefon',
        };
    }
}
