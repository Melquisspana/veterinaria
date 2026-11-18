<?php

namespace App\Notifications;

use App\Enums\VaccinationKind;
use App\Models\Vaccination;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VaccinationDueReminder extends Notification
{
    use Queueable;

    public function __construct(public Vaccination $vaccination) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $vaccination = $this->vaccination;
        $what = $vaccination->kind === VaccinationKind::Deworming ? 'la desparasitación' : "la vacuna {$vaccination->product}";

        return (new MailMessage)
            ->subject("A {$vaccination->patient->name} le toca {$what}")
            ->greeting("Hola {$notifiable->name},")
            ->line("A **{$vaccination->patient->name}** le corresponde {$what} el **{$vaccination->next_due_at->translatedFormat('j \d\e F')}**.")
            ->line('Responda este correo o escríbanos por WhatsApp para agendar la visita a domicilio.')
            ->salutation('¡Saludos! – '.config('app.name'));
    }
}
