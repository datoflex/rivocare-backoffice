<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TherapyApproach: string implements HasLabel
{
    case Behavioral = 'behavioral';
    case Psychodynamic = 'psychodynamic';
    case Psychoanalytic = 'psychoanalytic';
    case Systemic = 'systemic';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Behavioral => 'Verhaltenstherapie (VT)',
            self::Psychodynamic => 'Tiefenpsychologisch fundiert (TP)',
            self::Psychoanalytic => 'Analytische Psychotherapie (AP)',
            self::Systemic => 'Systemische Therapie (ST)',
            self::Other => 'Sonstiges',
        };
    }
}
