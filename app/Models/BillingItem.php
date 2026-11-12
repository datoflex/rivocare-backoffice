<?php

namespace App\Models;

use App\Enums\BillingStatus;
use App\Enums\BillingType;
use App\Models\Concerns\AuditsChanges;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Money owed to us by a company (receivable) or by us to a therapist (payable).
 */
#[Fillable([
    'placement_id', 'company_id', 'therapist_id', 'type', 'amount', 'status',
    'invoice_number', 'invoiced_on', 'paid_on', 'note',
])]
class BillingItem extends Model
{
    use AuditsChanges;

    protected function casts(): array
    {
        return [
            'type' => BillingType::class,
            'status' => BillingStatus::class,
            'amount' => 'decimal:2',
            'invoiced_on' => 'date',
            'paid_on' => 'date',
        ];
    }

    public function isReceivable(): bool
    {
        return $this->type->isReceivable();
    }

    public function placement(): BelongsTo
    {
        return $this->belongsTo(Placement::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(Therapist::class);
    }
}
