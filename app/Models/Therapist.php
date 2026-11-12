<?php

namespace App\Models;

use App\Enums\Capacity;
use App\Enums\Gender;
use App\Enums\TherapistStatus;
use App\Models\Concerns\AuditsChanges;
use App\Models\Concerns\HasActivityRecords;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'title', 'first_name', 'last_name', 'gender', 'email', 'phone', 'practice_name', 'street', 'postal_code', 'city',
    'offers_online', 'offers_in_person', 'insurance_types', 'approaches', 'focus_areas', 'languages',
    'status', 'capacity', 'capacity_note', 'first_session_fee', 'agreement_signed_on', 'notes',
])]
class Therapist extends Model
{
    use AuditsChanges, HasActivityRecords, HasFactory;

    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
            'status' => TherapistStatus::class,
            'capacity' => Capacity::class,
            'offers_online' => 'boolean',
            'offers_in_person' => 'boolean',
            'insurance_types' => 'array',
            'approaches' => 'array',
            'focus_areas' => 'array',
            'languages' => 'array',
            'first_session_fee' => 'decimal:2',
            'agreement_signed_on' => 'date',
        ];
    }

    protected function fullName(): Attribute
    {
        return Attribute::get(fn (): string => trim("{$this->title} {$this->first_name} {$this->last_name}"));
    }

    public function placements(): HasMany
    {
        return $this->hasMany(Placement::class);
    }

    public function candidacies(): HasMany
    {
        return $this->hasMany(PlacementCandidate::class);
    }

    public function billingItems(): HasMany
    {
        return $this->hasMany(BillingItem::class);
    }
}
