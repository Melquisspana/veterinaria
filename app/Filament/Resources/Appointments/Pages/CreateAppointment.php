<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Enums\AppointmentType;
use App\Filament\Resources\Appointments\AppointmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    /**
     * Permite precargar mascota y tipo desde enlaces (?patient=ID&type=vaccination), p. ej. desde "Agendar" en vacunas por vencer.
     */
    protected function afterFill(): void
    {
        $patientId = request()->integer('patient');
        $type = AppointmentType::tryFrom((string) request()->query('type'));

        $this->form->fill([
            ...$this->form->getRawState(),
            ...array_filter([
                'patient_id' => $patientId ?: null,
                'type' => $type?->value,
            ]),
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
