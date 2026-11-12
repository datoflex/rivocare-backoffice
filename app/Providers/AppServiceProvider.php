<?php

namespace App\Providers;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Field;
use Filament\Infolists\Components\Entry;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Table;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureFilamentForGerman();
    }

    /**
     * Labels generated from attribute names ("first_name" → "First name") are
     * looked up in lang/de.json. German formats for dates, numbers and money.
     */
    private function configureFilamentForGerman(): void
    {
        foreach ([Field::class, Entry::class, Column::class, BaseFilter::class] as $component) {
            $component::configureUsing(fn ($component) => $component->translateLabel());
        }

        foreach ([Table::class, Schema::class] as $container) {
            $container::configureUsing(fn ($container) => $container
                ->defaultDateDisplayFormat('d.m.Y')
                ->defaultDateTimeDisplayFormat('d.m.Y H:i')
                ->defaultNumberLocale('de')
                ->defaultCurrency('EUR'));
        }

        DateTimePicker::configureUsing(fn (DateTimePicker $picker) => $picker
            ->defaultDateDisplayFormat('d.m.Y')
            ->defaultDateTimeDisplayFormat('d.m.Y H:i'));
    }
}
