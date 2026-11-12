<?php

namespace App\Filament\Actions;

use App\Enums\CloseReason;
use App\Enums\PlacementStage;
use App\Models\Placement;
use App\Models\Therapist;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;

/**
 * Moves a placement to another stage and asks for what that stage needs:
 * a therapist from "First session scheduled" on, a reason when closing.
 */
class ChangeStageAction
{
    public static function make(string $name = 'changeStage'): Action
    {
        return Action::make($name)
            ->label('Phase ändern')
            ->icon(Heroicon::OutlinedArrowsRightLeft)
            ->modalWidth('lg')
            ->fillForm(fn (Placement $record): array => [
                'stage' => $record->stage,
                'therapist_id' => $record->therapist_id,
                'first_session_at' => $record->first_session_at,
                'close_reason' => $record->close_reason,
            ])
            ->schema([
                Select::make('stage')
                    ->options(PlacementStage::class)
                    ->required()
                    ->live(),
                Select::make('therapist_id')
                    ->label('Therapeut:in')
                    ->options(fn (): array => Therapist::query()
                        ->orderBy('last_name')
                        ->get()
                        ->mapWithKeys(fn (Therapist $therapist): array => [$therapist->id => $therapist->full_name])
                        ->all())
                    ->searchable()
                    ->visible(fn (Get $get): bool => (bool) PlacementStage::fromState($get('stage'))?->requiresTherapist())
                    ->required(fn (Get $get): bool => (bool) PlacementStage::fromState($get('stage'))?->requiresTherapist()),
                DateTimePicker::make('first_session_at')
                    ->label('Erste Sitzung')
                    ->seconds(false)
                    ->visible(fn (Get $get): bool => PlacementStage::fromState($get('stage')) === PlacementStage::FirstSessionScheduled),
                Select::make('close_reason')
                    ->options(CloseReason::class)
                    ->visible(fn (Get $get): bool => PlacementStage::fromState($get('stage')) === PlacementStage::Closed)
                    ->required(fn (Get $get): bool => PlacementStage::fromState($get('stage')) === PlacementStage::Closed),
            ])
            ->action(function (Placement $record, array $data): void {
                $record->update($data);
            })
            ->successNotificationTitle('Phase aktualisiert');
    }
}
