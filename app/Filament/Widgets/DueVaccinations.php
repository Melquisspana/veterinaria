<?php

namespace App\Filament\Widgets;

use App\Enums\AppointmentType;
use App\Filament\Resources\Appointments\AppointmentResource;
use App\Filament\Resources\Patients\PatientResource;
use App\Models\Vaccination;
use Filament\Actions\Action;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class DueVaccinations extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $days = config('vet.vaccine_reminder_days');

        return $table
            ->heading("Vacunas y desparasitaciones por vencer (vencidas y próximos {$days} días)")
            ->query(fn () => Vaccination::query()
                ->with('patient.owner')
                ->dueWithin($days, includeOverdue: true)
                ->whereDate('next_due_at', '>=', today()->subDays(60))
                ->orderBy('next_due_at'))
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('Nada por vencer')
            ->emptyStateIcon('heroicon-o-shield-check')
            ->columns([
                Split::make([
                    TextColumn::make('next_due_at')
                        ->label('Vence')
                        ->date('d/m/Y')
                        ->color(fn (Vaccination $record): string => $record->next_due_at->isPast() ? 'danger' : 'warning')
                        ->description(fn (Vaccination $record): string => $record->next_due_at->isToday() ? 'hoy' : $record->next_due_at->diffForHumans()),
                    TextColumn::make('patient.name')
                        ->label('Mascota')
                        ->weight('bold')
                        ->description(fn (Vaccination $record): string => $record->patient->owner->name)
                        ->url(fn (Vaccination $record): string => PatientResource::getUrl('view', ['record' => $record->patient_id])),
                    TextColumn::make('product')->label('Producto'),
                    TextColumn::make('reminder_sent_at')
                        ->label('Aviso por correo')
                        ->since()
                        ->prefix('Correo: ')
                        ->placeholder('Correo no enviado')
                        ->color('gray'),
                ])->from('md'),
            ])
            ->recordActions([
                Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->url(fn (Vaccination $record): ?string => $record->whatsappLink(), shouldOpenInNewTab: true)
                    ->visible(fn (Vaccination $record): bool => filled($record->whatsappLink())),
                Action::make('schedule')
                    ->label('Agendar')
                    ->icon('heroicon-o-calendar-days')
                    ->url(fn (Vaccination $record): string => AppointmentResource::getUrl('create', [
                        'patient' => $record->patient_id,
                        'type' => $record->kind->value === 'deworming' ? AppointmentType::Deworming->value : AppointmentType::Vaccination->value,
                    ])),
            ]);
    }
}
