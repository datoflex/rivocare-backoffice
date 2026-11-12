<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TaskStatus: string implements HasColor, HasLabel
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Waiting = 'waiting';
    case Done = 'done';

    public function getLabel(): string
    {
        return match ($this) {
            self::Open => 'Offen',
            self::InProgress => 'In Arbeit',
            self::Waiting => 'Wartet',
            self::Done => 'Erledigt',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Open => 'gray',
            self::InProgress => 'info',
            self::Waiting => 'warning',
            self::Done => 'success',
        };
    }
}
