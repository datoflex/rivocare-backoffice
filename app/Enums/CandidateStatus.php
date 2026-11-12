<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CandidateStatus: string implements HasColor, HasLabel
{
    case Contacted = 'contacted';
    case Available = 'available';
    case Unavailable = 'unavailable';
    case Proposed = 'proposed';
    case Chosen = 'chosen';
    case Declined = 'declined';

    public function getLabel(): string
    {
        return match ($this) {
            self::Contacted => 'Angefragt',
            self::Available => 'Hat Kapazität',
            self::Unavailable => 'Keine Kapazität',
            self::Proposed => 'Vorschlag an Klient:in',
            self::Chosen => 'Von Klient:in gewählt',
            self::Declined => 'Von Klient:in abgelehnt',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Contacted => 'gray',
            self::Available => 'info',
            self::Unavailable => 'danger',
            self::Proposed => 'warning',
            self::Chosen => 'success',
            self::Declined => 'gray',
        };
    }
}
