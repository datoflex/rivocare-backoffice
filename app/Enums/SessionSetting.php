<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SessionSetting: string implements HasLabel
{
    case Either = 'either';
    case Online = 'online';
    case InPerson = 'in_person';

    public function getLabel(): string
    {
        return match ($this) {
            self::Either => 'Online oder vor Ort',
            self::Online => 'Nur online',
            self::InPerson => 'Nur vor Ort',
        };
    }
}
