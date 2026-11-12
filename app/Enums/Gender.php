<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Gender: string implements HasLabel
{
    case Female = 'female';
    case Male = 'male';
    case Diverse = 'diverse';

    public function getLabel(): string
    {
        return match ($this) {
            self::Female => 'Weiblich',
            self::Male => 'Männlich',
            self::Diverse => 'Divers',
        };
    }
}
