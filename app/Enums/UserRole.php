<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasColor, HasLabel
{
    case Admin = 'admin';
    case Team = 'team';

    public function getLabel(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Team => 'Team',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Admin => 'danger',
            self::Team => 'gray',
        };
    }
}
