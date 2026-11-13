<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Enums\AppointmentStatus;
use App\Filament\Resources\Appointments\AppointmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAppointments extends ListRecords
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Agendar visita'),
        ];
    }

    /** @return array<string, Tab> */
    public function getTabs(): array
    {
        return [
            'upcoming' => Tab::make('Próximas')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('status', AppointmentStatus::Scheduled)
                    ->where('scheduled_at', '>=', today())
                    ->reorder('scheduled_at')),
            'today' => Tab::make('Hoy')
                ->modifyQueryUsing(fn (Builder $query) => $query->onDate(today())->reorder('scheduled_at')),
            'overdue' => Tab::make('Sin cerrar')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('status', AppointmentStatus::Scheduled)
                    ->where('scheduled_at', '<', today())),
            'completed' => Tab::make('Realizadas')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', AppointmentStatus::Completed)),
            'all' => Tab::make('Todas'),
        ];
    }
}
