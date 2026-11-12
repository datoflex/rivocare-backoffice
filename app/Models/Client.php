<?php

namespace App\Models;

use App\Enums\ContactChannel;
use App\Enums\Gender;
use App\Enums\InsuranceType;
use App\Enums\SessionSetting;
use App\Models\Concerns\AuditsChanges;
use App\Models\Concerns\HasActivityRecords;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An employee of a company looking for a therapist.
 *
 * Never store clinical or topic-related information here; it is kept
 * off-system and linked by the placement reference.
 */
#[Fillable([
    'company_id', 'first_name', 'last_name', 'email', 'phone', 'preferred_contact', 'languages',
    'insurance_type', 'preferred_therapist_gender', 'session_setting', 'postal_code', 'city',
    'consent_given_at', 'consent_withdrawn_at', 'notes',
])]
class Client extends Model
{
    use AuditsChanges, HasActivityRecords, HasFactory;

    protected function casts(): array
    {
        return [
            'email' => 'encrypted',
            'phone' => 'encrypted',
            'notes' => 'encrypted',
            'languages' => 'array',
            'preferred_contact' => ContactChannel::class,
            'insurance_type' => InsuranceType::class,
            'preferred_therapist_gender' => Gender::class,
            'session_setting' => SessionSetting::class,
            'consent_given_at' => 'datetime',
            'consent_withdrawn_at' => 'datetime',
        ];
    }

    protected function fullName(): Attribute
    {
        return Attribute::get(fn (): string => "{$this->first_name} {$this->last_name}");
    }

    public function hasValidConsent(): bool
    {
        return $this->consent_given_at !== null && $this->consent_withdrawn_at === null;
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function placements(): HasMany
    {
        return $this->hasMany(Placement::class);
    }
}
