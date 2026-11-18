<?php

namespace App\Filament\Widgets;

use App\Enums\AppointmentStatus;
use App\Filament\Actions\CompleteAppointmentAction;
use App\Models\Appointment;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Data\FetchInfo;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class AppointmentsCalendar extends FullCalendarWidget
{
    public Model|string|null $model = Appointment::class;

    protected ?string $startAttribute = 'scheduled_at';

    protected ?string $endAttribute = null;

    protected int|string|array $columnSpan = 'full';

    /** @return array<int, EventData> */
    public function fetchEvents(FetchInfo $info): array
    {
        return Appointment::query()
            ->with('patient.owner')
            ->where('status', '!=', AppointmentStatus::Cancelled)
            ->where('scheduled_at', '<', $info->end)
            ->where('scheduled_at', '>=', $info->start->subDay())
            ->get()
            ->map(fn (Appointment $appointment): EventData => EventData::make()
                ->id($appointment->id)
                ->title("{$appointment->patient->name} · {$appointment->type->getLabel()}")
                ->start($appointment->scheduled_at)
                ->end($appointment->endsAt())
                ->color($appointment->status === AppointmentStatus::Completed ? 'gray' : $appointment->type->getColor())
                ->tooltip(array_filter([
                    $appointment->patient->owner->name,
                    $appointment->effectiveAddress(),
                    $appointment->status->getLabel(),
                ])))
            ->all();
    }

    /** @return array<string, mixed> */
    public function config(): array
    {
        return [
            'initialView' => 'timeGridWeek',
            'mobileInitialView' => 'listWeek',
            'firstDay' => 1,
            'slotMinTime' => '07:00:00',
            'slotMaxTime' => '20:00:00',
            'allDaySlot' => false,
            'selectable' => true,
            'editable' => true,
            'durationEditable' => false,
            'nowIndicator' => true,
        ];
    }

    public function getTimezone(): string
    {
        return config('app.timezone');
    }

    protected function headerActions(): array
    {
        return [
            CreateAction::make()
                ->label('Agendar visita')
                ->mountUsing(function ($form, array $arguments): void {
                    $form->fill([
                        'scheduled_at' => $arguments['start'] ?? now()->addDay()->setTime(9, 0),
                        'duration_minutes' => 60,
                    ]);
                }),
        ];
    }

    protected function modalActions(): array
    {
        return [
            CompleteAppointmentAction::make()->cancelParentActions(),
            EditAction::make()->cancelParentActions(),
            DeleteAction::make()->cancelParentActions(),
        ];
    }
}
