<?php

namespace Database\Seeders;

use App\Enums\Capacity;
use App\Enums\CloseReason;
use App\Enums\CompanyStatus;
use App\Enums\Gender;
use App\Enums\InsuranceType;
use App\Enums\PlacementStage;
use App\Enums\SessionSetting;
use App\Enums\TherapistStatus;
use App\Enums\TherapyApproach;
use App\Models\Client;
use App\Models\Company;
use App\Models\Placement;
use App\Models\Therapist;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Fictional sample data for local development only.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $team = User::query()->get();
        $faker = fake('de_DE');

        $companies = collect([
            ['Nordlicht Logistik GmbH', 'Hamburg', '20095', 180, 450],
            ['Bergwerk Software AG', 'München', '80331', 200, 500],
            ['Rheinblick Versicherung', 'Köln', '50667', 180, 400],
        ])->map(function (array $row) use ($faker): Company {
            [$name, $city, $postalCode, $firstSessionPrice, $successFee] = $row;

            $company = Company::query()->create([
                'name' => $name,
                'status' => CompanyStatus::Active,
                'email' => 'kontakt@'.str($name)->before(' ')->lower()->ascii().'.example',
                'phone' => $faker->phoneNumber(),
                'street' => $faker->streetAddress(),
                'postal_code' => $postalCode,
                'city' => $city,
                'contract_starts_on' => now()->subMonths(rand(2, 12))->startOfMonth(),
                'first_session_price' => $firstSessionPrice,
                'success_fee' => $successFee,
            ]);

            $company->contacts()->create([
                'name' => $faker->name(),
                'role' => 'HR',
                'email' => 'hr@'.str($name)->before(' ')->lower()->ascii().'.example',
                'is_primary' => true,
            ]);

            return $company;
        });

        $therapists = collect(range(1, 10))->map(fn (int $i): Therapist => Therapist::query()->create([
            'title' => $i % 3 === 0 ? 'Dr.' : null,
            'first_name' => $faker->firstName(),
            'last_name' => $faker->lastName(),
            'gender' => $faker->randomElement(Gender::cases()),
            'email' => "praxis{$i}@therapie.example",
            'phone' => $faker->phoneNumber(),
            'practice_name' => 'Praxis für Psychotherapie',
            'street' => $faker->streetAddress(),
            'postal_code' => $faker->randomElement(['20095', '22767', '80331', '81675', '50667', '50823']),
            'city' => $faker->randomElement(['Hamburg', 'München', 'Köln']),
            'offers_online' => $faker->boolean(60),
            'offers_in_person' => true,
            'insurance_types' => $faker->randomElements(array_column(InsuranceType::cases(), 'value'), rand(1, 3)),
            'approaches' => $faker->randomElements(array_column(TherapyApproach::cases(), 'value'), rand(1, 2)),
            'focus_areas' => $faker->randomElements(['Angst', 'Burnout', 'Depression', 'Trauma', 'Paare', 'Trauer'], rand(1, 3)),
            'languages' => $faker->randomElements(['Deutsch', 'Englisch', 'Türkisch', 'Spanisch'], rand(1, 2)),
            'status' => TherapistStatus::Active,
            'capacity' => $faker->randomElement(Capacity::cases()),
            'first_session_fee' => 120,
            'agreement_signed_on' => now()->subMonths(rand(1, 10)),
        ]));

        // [stage to walk the case to, close reason]
        $journeys = [
            [PlacementStage::New, null],
            [PlacementStage::New, null],
            [PlacementStage::OnboardingCall, null],
            [PlacementStage::Matching, null],
            [PlacementStage::Proposed, null],
            [PlacementStage::FirstSessionScheduled, null],
            [PlacementStage::DecisionPending, null],
            [PlacementStage::Closed, CloseReason::IntroductionStarted],
        ];

        foreach ($journeys as [$target, $closeReason]) {
            $company = $companies->random();

            $client = Client::query()->create([
                'company_id' => $company->id,
                'first_name' => $faker->firstName(),
                'last_name' => $faker->lastName(),
                'email' => $faker->unique()->safeEmail(),
                'phone' => $faker->phoneNumber(),
                'languages' => ['Deutsch'],
                'insurance_type' => $faker->randomElement(InsuranceType::cases()),
                'session_setting' => $faker->randomElement(SessionSetting::cases()),
                'postal_code' => $company->postal_code,
                'city' => $company->city,
                'consent_given_at' => $target === PlacementStage::New ? null : now()->subDays(rand(5, 30)),
            ]);

            $placement = $client->placements()->create([
                'company_contact_id' => $company->contacts->first()->id,
                'assigned_user_id' => $team->random()->id,
            ]);

            $this->walk($placement, $target, $closeReason, $therapists->random());
        }
    }

    /**
     * Moves the case through every stage up to the target, so the workflow
     * creates the same tasks, timeline entries and billing items as in real use.
     */
    private function walk(Placement $placement, PlacementStage $target, ?CloseReason $closeReason, Therapist $therapist): void
    {
        if ($target === PlacementStage::New) {
            return;
        }

        foreach (PlacementStage::cases() as $stage) {
            if ($stage === PlacementStage::New) {
                continue;
            }

            if ($stage === PlacementStage::Proposed) {
                $placement->candidates()->create(['therapist_id' => $therapist->id, 'status' => 'proposed']);
            }

            // Completes the previous stage's checklist before moving on.
            $placement->tasks()->notDone()->get()->each->update(['status' => 'done']);

            $placement->update([
                'stage' => $stage,
                'therapist_id' => $stage->requiresTherapist() || $stage === PlacementStage::Closed ? $therapist->id : $placement->therapist_id,
                'first_session_at' => $stage === PlacementStage::FirstSessionScheduled ? now()->addDays(rand(3, 10))->setTime(10, 0) : $placement->first_session_at,
                'close_reason' => $stage === PlacementStage::Closed ? $closeReason : null,
            ]);

            if ($stage === $target) {
                return;
            }
        }
    }
}
