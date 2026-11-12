<?php

namespace App\Models;

use App\Enums\CloseReason;
use App\Enums\PlacementStage;
use App\Models\Concerns\AuditsChanges;
use App\Models\Concerns\HasActivityRecords;
use App\Observers\PlacementObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A case: one client, one need, one placement process.
 */
#[Fillable([
    'client_id', 'company_contact_id', 'assigned_user_id', 'therapist_id', 'stage', 'position',
    'first_session_at', 'closed_at', 'close_reason',
])]
#[ObservedBy(PlacementObserver::class)]
class Placement extends Model
{
    use AuditsChanges, HasActivityRecords, HasFactory;

    protected function casts(): array
    {
        return [
            'stage' => PlacementStage::class,
            'close_reason' => CloseReason::class,
            'first_session_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public static function nextReference(): string
    {
        $prefix = 'C-'.now()->year.'-';

        $last = static::query()
            ->where('reference', 'like', $prefix.'%')
            ->orderByDesc('reference')
            ->value('reference');

        $number = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    public function scopeOpen(Builder $query): void
    {
        $query->where('stage', '!=', PlacementStage::Closed);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function companyContact(): BelongsTo
    {
        return $this->belongsTo(CompanyContact::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(Therapist::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(PlacementCandidate::class);
    }

    public function billingItems(): HasMany
    {
        return $this->hasMany(BillingItem::class);
    }
}
