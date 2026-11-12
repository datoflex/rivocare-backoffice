<?php

namespace App\Filament\Widgets;

use App\Enums\BillingStatus;
use App\Enums\BillingType;
use App\Enums\PlacementStage;
use App\Enums\Responsible;
use App\Filament\Resources\BillingItems\BillingItemResource;
use App\Filament\Resources\Placements\PlacementResource;
use App\Filament\Resources\Tasks\TaskResource;
use App\Models\BillingItem;
use App\Models\Placement;
use App\Models\Task;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class OverviewStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $stats = [
            Stat::make('Offene Fälle', Placement::query()->open()->count())
                ->description(Placement::query()->open()->where('assigned_user_id', auth()->id())->count().' davon dir zugewiesen')
                ->icon(Heroicon::OutlinedBriefcase)
                ->url(PlacementResource::getUrl()),
            Stat::make('Diesen Monat vermittelt', Placement::query()
                ->where('stage', PlacementStage::Closed)
                ->where('close_reason', 'introduction_started')
                ->where('closed_at', '>=', now()->startOfMonth())
                ->count())
                ->description('Therapieeinstieg gestartet')
                ->icon(Heroicon::OutlinedCheckBadge)
                ->color('success'),
            Stat::make('Überfällige Aufgaben', $overdue = Task::query()->overdue()->count())
                ->description(Task::query()->overdue()->where('responsible', '!=', Responsible::Us)->count().' davon warten auf Klient:innen, Therapeut:innen oder Unternehmen')
                ->icon(Heroicon::OutlinedExclamationTriangle)
                ->color($overdue > 0 ? 'danger' : 'success')
                ->url(TaskResource::getUrl('index', ['filters' => ['overdue' => ['isActive' => true]]])),
        ];

        if (auth()->user()->isAdmin()) {
            $toInvoice = BillingItem::query()
                ->whereIn('type', [BillingType::FirstSession, BillingType::SuccessFee])
                ->where('status', BillingStatus::Open)
                ->sum('amount');

            $stats[] = Stat::make('Bereit zur Abrechnung', Number::currency((float) $toInvoice, 'EUR', 'de'))
                ->description('Offene Forderungen an Unternehmen')
                ->icon(Heroicon::OutlinedBanknotes)
                ->url(BillingItemResource::getUrl());
        }

        return $stats;
    }
}
