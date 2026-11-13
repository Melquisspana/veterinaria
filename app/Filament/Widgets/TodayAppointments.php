<?php

namespace App\Filament\Widgets;

use App\Filament\Actions\CompleteAppointmentAction;
use App\Filament\Resources\Appointments\AppointmentResource;
use App\Filament\Resources\Appointments\Tables\AppointmentsTable;
use App\Models\Appointment;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class TodayAppointments extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Ruta de hoy';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Appointment::query()
                ->with('patient.owner')
                ->onDate(today())
                ->where('status', '!=', 'cancelled')
                ->orderBy('scheduled_at'))
            ->paginated(false)
            ->emptyStateHeading('No hay visitas para hoy')
            ->emptyStateIcon('heroicon-o-sun')
            ->columns([
                Split::make([
                    TextColumn::make('scheduled_at')->label('Hora')->time('h:i A')->weight('bold')->grow(false),
                    TextColumn::make('patient.name')
                        ->label('Mascota')
                        ->weight('bold')
                        ->description(fn (Appointment $record): string => $record->patient->owner->name),
                    Stack::make([
                        TextColumn::make('type')->label('Tipo')->badge(),
                        TextColumn::make('status')->label('Estado')->badge(),
                    ])->space(1)->grow(false),
                    TextColumn::make('effective_address')
                        ->label('Dirección')
                        ->state(fn (Appointment $record): ?string => $record->effectiveAddress())
                        ->placeholder('—')
                        ->icon('heroicon-o-map-pin')
                        ->wrap()
                        ->url(fn (Appointment $record): ?string => $record->mapsLink(), shouldOpenInNewTab: true),
                ])->from('md'),
            ])
            ->recordUrl(fn (Appointment $record): string => AppointmentResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                CompleteAppointmentAction::make(),
                AppointmentsTable::mapsAction()->iconButton(),
                AppointmentsTable::whatsappAction()->iconButton(),
            ]);
    }
}
