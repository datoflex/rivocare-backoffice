<?php

namespace App\Models;

use App\Enums\CompanyStatus;
use App\Models\Concerns\AuditsChanges;
use App\Models\Concerns\HasActivityRecords;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable([
    'name', 'status', 'email', 'phone', 'website', 'street', 'postal_code', 'city', 'billing_email',
    'contract_starts_on', 'contract_ends_on', 'first_session_price', 'success_fee', 'notes',
])]
class Company extends Model
{
    use AuditsChanges, HasActivityRecords, HasFactory;

    protected function casts(): array
    {
        return [
            'status' => CompanyStatus::class,
            'contract_starts_on' => 'date',
            'contract_ends_on' => 'date',
            'first_session_price' => 'decimal:2',
            'success_fee' => 'decimal:2',
        ];
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CompanyContact::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function placements(): HasManyThrough
    {
        return $this->hasManyThrough(Placement::class, Client::class);
    }

    public function billingItems(): HasMany
    {
        return $this->hasMany(BillingItem::class);
    }
}
