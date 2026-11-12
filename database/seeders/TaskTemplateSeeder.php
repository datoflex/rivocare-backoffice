<?php

namespace Database\Seeders;

use App\Enums\PlacementStage;
use App\Enums\Responsible;
use App\Models\TaskTemplate;
use Illuminate\Database\Seeder;

/**
 * Default stage checklists. Admins can change them under "Stage checklists".
 */
class TaskTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            PlacementStage::New->value => [
                ['Willkommens-E-Mail mit Einwilligungserklärung senden', Responsible::Us, 1],
                ['Unterschriebene Einwilligungserklärung zurücksenden', Responsible::Client, 5],
                ['Onboarding-Gespräch vereinbaren', Responsible::Us, 2],
            ],
            PlacementStage::OnboardingCall->value => [
                ['Onboarding-Gespräch führen', Responsible::Us, 3],
                ['Matching-Präferenzen bei der Klient:in erfassen', Responsible::Us, 3],
            ],
            PlacementStage::Matching->value => [
                ['2–3 passende Therapeut:innen auswählen', Responsible::Us, 2],
                ['Ausgewählte Therapeut:innen nach Kapazität fragen', Responsible::Us, 3],
                ['Kapazität zurückmelden', Responsible::Therapist, 6],
            ],
            PlacementStage::Proposed->value => [
                ['Vorschlag an Klient:in senden', Responsible::Us, 1],
                ['Therapeut:in auswählen', Responsible::Client, 5],
            ],
            PlacementStage::FirstSessionScheduled->value => [
                ['Termin mit Klient:in und Therapeut:in bestätigen', Responsible::Us, 1],
                ['Bestätigen, dass die erste Sitzung stattgefunden hat', Responsible::Therapist, 14],
            ],
            PlacementStage::DecisionPending->value => [
                ['Nach der ersten Sitzung bei Klient:in nachfragen', Responsible::Us, 3],
                ['Bestätigen, ob der Therapieeinstieg startet', Responsible::Therapist, 14],
            ],
            PlacementStage::Closed->value => [
                ['Notizen außerhalb des Systems zu diesem Fall löschen', Responsible::Us, 30],
            ],
        ];

        foreach ($templates as $stage => $tasks) {
            foreach ($tasks as $sort => [$title, $responsible, $dueInDays]) {
                TaskTemplate::query()->updateOrCreate(
                    ['stage' => $stage, 'title' => $title],
                    ['responsible' => $responsible, 'due_in_days' => $dueInDays, 'sort' => $sort, 'is_active' => true],
                );
            }
        }
    }
}
