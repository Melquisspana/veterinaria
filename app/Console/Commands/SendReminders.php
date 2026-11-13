<?php

namespace App\Console\Commands;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Vaccination;
use App\Notifications\AppointmentReminder;
use App\Notifications\VaccinationDueReminder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('vet:send-reminders')]
#[Description('Envía por correo los recordatorios de visitas de mañana y de vacunas por vencer')]
class SendReminders extends Command
{
    public function handle(): int
    {
        $appointments = Appointment::query()
            ->with('patient.owner')
            ->where('status', AppointmentStatus::Scheduled)
            ->onDate(today()->addDay())
            ->whereNull('reminder_sent_at')
            ->whereHas('patient.owner', fn ($query) => $query->whereNotNull('email')->where('email', '!=', ''))
            ->get();

        foreach ($appointments as $appointment) {
            $appointment->patient->owner->notify(new AppointmentReminder($appointment));
            $appointment->update(['reminder_sent_at' => now()]);
        }

        $vaccinations = Vaccination::query()
            ->with('patient.owner')
            ->dueWithin(config('vet.vaccine_reminder_days'))
            ->whereNull('reminder_sent_at')
            ->whereHas('patient.owner', fn ($query) => $query->whereNotNull('email')->where('email', '!=', ''))
            ->get();

        foreach ($vaccinations as $vaccination) {
            $vaccination->patient->owner->notify(new VaccinationDueReminder($vaccination));
            $vaccination->update(['reminder_sent_at' => now()]);
        }

        $this->info("Recordatorios enviados: {$appointments->count()} visitas, {$vaccinations->count()} vacunas.");

        return self::SUCCESS;
    }
}
