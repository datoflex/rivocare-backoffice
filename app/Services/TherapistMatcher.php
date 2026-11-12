<?php

namespace App\Services;

use App\Enums\Capacity;
use App\Enums\SessionSetting;
use App\Enums\TherapistStatus;
use App\Models\Client;
use App\Models\Therapist;
use Illuminate\Database\Eloquent\Builder;

/**
 * Finds therapists that fit a client's practical preferences.
 * Therapists in the client's postal region are listed first.
 */
class TherapistMatcher
{
    /**
     * @return Builder<Therapist>
     */
    public function query(Client $client): Builder
    {
        $query = Therapist::query()
            ->where('status', TherapistStatus::Active)
            ->where('capacity', '!=', Capacity::Full);

        match ($client->session_setting) {
            SessionSetting::Online => $query->where('offers_online', true),
            SessionSetting::InPerson => $query->where('offers_in_person', true),
            default => null,
        };

        if ($client->insurance_type !== null) {
            $query->whereJsonContains('insurance_types', $client->insurance_type->value);
        }

        if ($client->preferred_therapist_gender !== null) {
            $query->where('gender', $client->preferred_therapist_gender);
        }

        if (filled($client->languages)) {
            $query->where(function (Builder $query) use ($client): void {
                foreach ($client->languages as $language) {
                    $query->orWhereJsonContains('languages', $language);
                }
            });
        }

        if (filled($client->postal_code)) {
            $query->orderByRaw(
                'CASE WHEN substr(postal_code, 1, 2) = ? THEN 0 ELSE 1 END',
                [substr($client->postal_code, 0, 2)],
            );
        }

        return $query->orderBy('capacity')->orderBy('last_name');
    }
}
