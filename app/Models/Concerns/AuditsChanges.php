<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Writes an audit trail of changes to the activity log.
 *
 * Attributes with an `encrypted` cast are never written to the log,
 * otherwise their decrypted values would end up there in plain text.
 */
trait AuditsChanges
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        $encrypted = array_keys(array_filter(
            $this->getCasts(),
            fn (string $cast): bool => str_starts_with($cast, 'encrypted'),
        ));

        return LogOptions::defaults()
            ->logFillable()
            ->logExcept([...$encrypted, 'position', 'password', 'remember_token'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
