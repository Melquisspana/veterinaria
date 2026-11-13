<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Owner;
use App\Models\Patient;
use App\Models\Vaccination;
use App\Notifications\AppointmentReminder;
use App\Notifications\VaccinationDueReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendRemindersTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_emails_owners_about_tomorrows_visits_and_due_vaccines_once(): void
    {
        Notification::fake();

        $owner = Owner::factory()->create(['email' => 'duena@example.com']);
        $patient = Patient::factory()->for($owner)->create();

        $tomorrow = Appointment::factory()->for($patient)->create(['scheduled_at' => today()->addDay()->setTime(10, 0)]);
        Appointment::factory()->for($patient)->create(['scheduled_at' => today()->addDays(3)->setTime(10, 0)]);
        Appointment::factory()->for($patient)->create(['scheduled_at' => today()->addDay()->setTime(15, 0), 'status' => AppointmentStatus::Cancelled]);
        $vaccine = Vaccination::factory()->for($patient)->create(['applied_at' => today()->subYear(), 'next_due_at' => today()->addDays(5)]);

        $this->artisan('vet:send-reminders')->assertSuccessful();

        Notification::assertSentTo($owner, AppointmentReminder::class, fn (AppointmentReminder $n) => $n->appointment->is($tomorrow));
        Notification::assertSentTo($owner, VaccinationDueReminder::class, fn (VaccinationDueReminder $n) => $n->vaccination->is($vaccine));
        Notification::assertCount(2);
        $this->assertNotNull($tomorrow->fresh()->reminder_sent_at);
        $this->assertNotNull($vaccine->fresh()->reminder_sent_at);

        $this->artisan('vet:send-reminders')->assertSuccessful();
        Notification::assertCount(2);
    }

    public function test_owners_without_email_are_skipped(): void
    {
        Notification::fake();

        $patient = Patient::factory()->for(Owner::factory()->state(['email' => null]))->create();
        Appointment::factory()->for($patient)->create(['scheduled_at' => today()->addDay()->setTime(10, 0)]);

        $this->artisan('vet:send-reminders')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_reminder_email_renders_in_spanish(): void
    {
        $appointment = Appointment::factory()->create(['scheduled_at' => today()->addDay()->setTime(10, 0)]);

        $mail = (new AppointmentReminder($appointment))->toMail($appointment->patient->owner);

        $this->assertStringContainsString($appointment->patient->name, $mail->subject);
        $this->assertStringContainsString('Hola', $mail->greeting);
    }
}
