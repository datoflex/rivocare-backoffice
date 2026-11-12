<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CloseReason: string implements HasColor, HasLabel
{
    case IntroductionStarted = 'introduction_started';
    case NotContinued = 'not_continued';
    case ClientWithdrew = 'client_withdrew';
    case NoSuitableTherapist = 'no_suitable_therapist';
    case ClientUnreachable = 'client_unreachable';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::IntroductionStarted => 'Therapieeinstieg gestartet (vermittelt)',
            self::NotContinued => 'Nach erster Sitzung nicht fortgesetzt',
            self::ClientWithdrew => 'Klient:in hat abgesagt',
            self::NoSuitableTherapist => 'Kein passendes Therapieangebot',
            self::ClientUnreachable => 'Klient:in nicht erreichbar',
            self::Other => 'Sonstiges',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::IntroductionStarted => 'success',
            self::NotContinued => 'warning',
            self::ClientWithdrew => 'gray',
            self::NoSuitableTherapist => 'danger',
            self::ClientUnreachable => 'gray',
            self::Other => 'gray',
        };
    }
}
