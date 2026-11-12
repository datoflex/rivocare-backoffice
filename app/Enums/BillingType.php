<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BillingType: string implements HasColor, HasLabel
{
    case FirstSession = 'first_session';
    case SuccessFee = 'success_fee';
    case TherapistFee = 'therapist_fee';

    public function getLabel(): string
    {
        return match ($this) {
            self::FirstSession => 'Erste Sitzung (Unternehmen)',
            self::SuccessFee => 'Vermittlungsgebühr (Unternehmen)',
            self::TherapistFee => 'Honorar erste Sitzung (Therapeut:in)',
        };
    }

    /**
     * Receivables are paid to us by the company; the therapist fee is paid by us.
     */
    public function isReceivable(): bool
    {
        return $this !== self::TherapistFee;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::FirstSession => 'info',
            self::SuccessFee => 'success',
            self::TherapistFee => 'warning',
        };
    }
}
