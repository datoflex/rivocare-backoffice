<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BillingStatus: string implements HasColor, HasLabel
{
    case Open = 'open';
    case Invoiced = 'invoiced';
    case Paid = 'paid';

    public function getLabel(): string
    {
        return match ($this) {
            self::Open => 'Offen',
            self::Invoiced => 'Abgerechnet',
            self::Paid => 'Bezahlt',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Open => 'gray',
            self::Invoiced => 'warning',
            self::Paid => 'success',
        };
    }
}
