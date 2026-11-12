<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PlacementStage: string implements HasColor, HasLabel
{
    case New = 'new';
    case OnboardingCall = 'onboarding_call';
    case Matching = 'matching';
    case Proposed = 'proposed';
    case FirstSessionScheduled = 'first_session_scheduled';
    case DecisionPending = 'decision_pending';
    case Closed = 'closed';

    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'Neu',
            self::OnboardingCall => 'Onboarding-Gespräch',
            self::Matching => 'Matching',
            self::Proposed => 'Vorschlag an Klient:in',
            self::FirstSessionScheduled => 'Erste Sitzung geplant',
            self::DecisionPending => 'Entscheidung offen',
            self::Closed => 'Abgeschlossen',
        };
    }

    /**
     * Form state can hold either the enum or its raw value.
     */
    public static function fromState(mixed $state): ?self
    {
        return $state instanceof self ? $state : self::tryFrom((string) $state);
    }

    public function requiresTherapist(): bool
    {
        return in_array($this, [self::FirstSessionScheduled, self::DecisionPending], true);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::New => 'gray',
            self::OnboardingCall => 'info',
            self::Matching => 'primary',
            self::Proposed => 'warning',
            self::FirstSessionScheduled => 'info',
            self::DecisionPending => 'warning',
            self::Closed => 'success',
        };
    }
}
