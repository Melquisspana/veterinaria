<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Filament\Resources\Owners\OwnerResource;
use App\Filament\Resources\Patients\PatientResource;
use App\Models\Appointment;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AppointmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Visita')
                    ->columns(['default' => 2, 'md' => 3])
                    ->schema([
                        TextEntry::make('scheduled_at')->label('Fecha y hora')->dateTime('d/m/Y h:i A'),
                        TextEntry::make('type')->label('Tipo')->badge(),
                        TextEntry::make('status')->label('Estado')->badge(),
                        TextEntry::make('patient.name')
                            ->label('Mascota')
                            ->url(fn (Appointment $record): string => PatientResource::getUrl('view', ['record' => $record->patient_id])),
                        TextEntry::make('patient.owner.name')
                            ->label('Dueño')
                            ->url(fn (Appointment $record): string => OwnerResource::getUrl('view', ['record' => $record->patient->owner_id])),
                        TextEntry::make('patient.owner.phone')->label('Teléfono')->placeholder('—'),
                        TextEntry::make('effective_address')
                            ->label('Dirección')
                            ->state(fn (Appointment $record): ?string => $record->effectiveAddress())
                            ->placeholder('—')
                            ->icon('heroicon-o-map-pin')
                            ->url(fn (Appointment $record): ?string => $record->mapsLink(), shouldOpenInNewTab: true)
                            ->columnSpanFull(),
                        TextEntry::make('patient.owner.address_reference')->label('Referencia')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('notes')->label('Notas')->placeholder('—')->columnSpanFull(),
                    ]),
                Section::make('Resultado de la visita')
                    ->visible(fn (Appointment $record): bool => $record->medicalRecord !== null)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('medicalRecord.reason')->label('Motivo'),
                        TextEntry::make('medicalRecord.diagnosis')->label('Diagnóstico')->placeholder('—'),
                        TextEntry::make('medicalRecord.treatment')->label('Tratamiento')->placeholder('—')->columnSpanFull(),
                        RepeatableEntry::make('vaccinations')
                            ->label('Aplicaciones')
                            ->columns(3)
                            ->schema([
                                TextEntry::make('product')->hiddenLabel(),
                                TextEntry::make('kind')->hiddenLabel()->badge(),
                                TextEntry::make('next_due_at')->hiddenLabel()->date('d/m/Y')->prefix('Próxima: ')->placeholder('—'),
                            ])
                            ->columnSpanFull()
                            ->visible(fn (Appointment $record): bool => $record->vaccinations->isNotEmpty()),
                        TextEntry::make('payment.amount')->label('Cobro')->money(config('vet.currency'))->placeholder('Sin cobro'),
                        TextEntry::make('payment.status')->label('Estado del pago')->badge()->placeholder('—'),
                    ]),
            ]);
    }
}
