<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Actions\CompleteAppointmentAction;
use App\Filament\Resources\Appointments\AppointmentResource;
use App\Filament\Resources\Appointments\Tables\AppointmentsTable;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAppointment extends ViewRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CompleteAppointmentAction::make()->after(fn () => $this->refreshFormData(['status'])),
            AppointmentsTable::whatsappAction(),
            AppointmentsTable::mapsAction(),
            EditAction::make(),
        ];
    }
}
