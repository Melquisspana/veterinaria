<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AppointmentsCalendar;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class Calendar extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Agenda';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Calendario';

    protected static ?string $title = 'Calendario de visitas';

    protected static ?string $slug = 'calendario';

    protected function getHeaderWidgets(): array
    {
        return [
            AppointmentsCalendar::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }
}
