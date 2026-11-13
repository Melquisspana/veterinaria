<?php

namespace App\Filament\Resources\Appointments\Tables;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Filament\Actions\CompleteAppointmentAction;
use App\Models\Appointment;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AppointmentsTable
{
    public static function configure(Table $table, bool $showPatient = true): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('patient.owner'))
            ->defaultSort('scheduled_at', 'desc')
            ->columns([
                Split::make([
                    TextColumn::make('scheduled_at')
                        ->label('Fecha')
                        ->dateTime('D d/m/Y · h:i A')
                        ->sortable()
                        ->weight('bold')
                        ->grow(false),
                    TextColumn::make('patient.name')
                        ->label('Mascota')
                        ->searchable()
                        ->weight('bold')
                        ->description(fn (Appointment $record): ?string => $record->patient->owner->name)
                        ->visible($showPatient),
                    Stack::make([
                        TextColumn::make('type')->label('Tipo')->badge(),
                        TextColumn::make('status')->label('Estado')->badge(),
                    ])->space(1)->grow(false),
                    TextColumn::make('effective_address')
                        ->label('Dirección')
                        ->state(fn (Appointment $record): ?string => $record->effectiveAddress())
                        ->limit(45)
                        ->icon('heroicon-o-map-pin')
                        ->placeholder('—')
                        ->url(fn (Appointment $record): ?string => $record->mapsLink(), shouldOpenInNewTab: true)
                        ->visible($showPatient)
                        ->visibleFrom('lg'),
                ])->from('md'),
            ])
            ->filters([
                SelectFilter::make('type')->label('Tipo')->options(AppointmentType::class),
                SelectFilter::make('status')->label('Estado')->options(AppointmentStatus::class),
            ])
            ->recordActions([
                CompleteAppointmentAction::make(),
                ActionGroup::make([
                    self::whatsappAction(),
                    self::mapsAction(),
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('cancel')
                        ->label('Cancelar visita')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn (Appointment $record): bool => $record->status === AppointmentStatus::Scheduled)
                        ->action(fn (Appointment $record) => $record->update(['status' => AppointmentStatus::Cancelled])),
                ]),
            ]);
    }

    public static function whatsappAction(): Action
    {
        return Action::make('whatsapp')
            ->label('Recordar por WhatsApp')
            ->icon('heroicon-o-chat-bubble-left-right')
            ->color('success')
            ->url(fn (Appointment $record): ?string => $record->whatsappLink(), shouldOpenInNewTab: true)
            ->visible(fn (Appointment $record): bool => $record->status === AppointmentStatus::Scheduled && filled($record->whatsappLink()));
    }

    public static function mapsAction(): Action
    {
        return Action::make('maps')
            ->label('Cómo llegar')
            ->icon('heroicon-o-map-pin')
            ->color('info')
            ->url(fn (Appointment $record): ?string => $record->mapsLink(), shouldOpenInNewTab: true)
            ->visible(fn (Appointment $record): bool => filled($record->mapsLink()));
    }
}
