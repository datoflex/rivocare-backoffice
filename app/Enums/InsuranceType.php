<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum InsuranceType: string implements HasColor, HasLabel
{
    case Statutory = 'statutory';
    case Private = 'private';
    case SelfPay = 'self_pay';

    public function getLabel(): string
    {
        return match ($this) {
            self::Statutory => 'Gesetzlich (GKV)',
            self::Private => 'Privat (PKV)',
            self::SelfPay => 'Selbstzahler',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Statutory => 'info',
            self::Private => 'primary',
            self::SelfPay => 'gray',
        };
    }
}
