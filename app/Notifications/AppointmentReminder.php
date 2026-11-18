<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentReminder extends Notification
{
    use Queueable;

    public function __construct(public Appointment $appointment) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appointment = $this->appointment;

        return (new MailMessage)
            ->subject("Recordatorio: visita para {$appointment->patient->name} mañana")
            ->greeting("Hola {$notifiable->name},")
            ->line("Le recordamos la visita de **{$appointment->type->getLabel()}** para **{$appointment->patient->name}**.")
            ->line('📅 '.ucfirst($appointment->scheduled_at->translatedFormat('l j \d\e F')).' a las '.$appointment->scheduled_at->format('g:i A'))
            ->line('📍 '.($appointment->effectiveAddress() ?? 'Dirección registrada'))
            ->line('Si necesita cambiar la hora, responda a este correo o escríbanos por WhatsApp.')
            ->salutation('¡Saludos! – '.config('app.name'));
    }
}
