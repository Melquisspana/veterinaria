<?php

namespace App\Filament\Actions;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Enums\PaymentMethod;
use App\Enums\VaccinationKind;
use App\Filament\Resources\Patients\Schemas\MedicalRecordForm;
use App\Filament\Resources\Patients\Schemas\VaccinationForm;
use App\Models\Appointment;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;

class CompleteAppointmentAction
{
    /**
     * Acción "Completar visita": registra consulta + vacunas + cobro y marca la visita como realizada.
     */
    public static function make(string $name = 'complete'): Action
    {
        return Action::make($name)
            ->label('Completar')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->modalHeading(fn (Appointment $record): string => "Completar visita de {$record->patient->name}")
            ->modalSubmitActionLabel('Guardar y marcar realizada')
            ->modalWidth('3xl')
            ->visible(fn (Appointment $record): bool => $record->status === AppointmentStatus::Scheduled)
            ->fillForm(fn (Appointment $record): array => [
                'record' => [
                    'reason' => $record->type->getLabel(),
                    'weight_kg' => $record->patient->weight_kg,
                ],
                'vaccinations' => match ($record->type) {
                    AppointmentType::Vaccination => [['kind' => VaccinationKind::Vaccine->value]],
                    AppointmentType::Deworming => [['kind' => VaccinationKind::Deworming->value]],
                    default => [],
                },
                'payment' => ['paid' => true, 'method' => PaymentMethod::Cash->value],
            ])
            ->schema([
                Section::make('Consulta')
                    ->statePath('record')
                    ->schema(MedicalRecordForm::fields(withDate: false)),
                Section::make('Vacunas / desparasitaciones aplicadas')
                    ->collapsible()
                    ->schema([
                        Repeater::make('vaccinations')
                            ->hiddenLabel()
                            ->schema(VaccinationForm::fields(withAppliedAt: false))
                            ->addActionLabel('Agregar aplicación')
                            ->defaultItems(0)
                            ->itemLabel(fn (array $state): ?string => $state['product'] ?? null),
                    ]),
                Section::make('Cobro')
                    ->statePath('payment')
                    ->columns(3)
                    ->schema([
                        TextInput::make('amount')
                            ->label('Monto')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('$')
                            ->helperText('Vacío = sin cobro.'),
                        Select::make('method')
                            ->label('Método')
                            ->options(PaymentMethod::class)
                            ->native(false),
                        Toggle::make('paid')
                            ->label('Ya pagó')
                            ->inline(false)
                            ->live(),
                    ]),
            ])
            ->action(function (Appointment $record, array $data): void {
                $record->complete(
                    medicalRecord: $data['record'],
                    vaccinations: array_values($data['vaccinations'] ?? []),
                    payment: $data['payment'] ?? null,
                );

                Notification::make()->title('Visita registrada')->success()->send();
            });
    }
}
