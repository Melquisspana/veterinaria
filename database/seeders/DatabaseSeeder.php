<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Owner;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vaccination;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            $this->seedProductionAdmin();

            return;
        }

        User::firstOrCreate(
            ['email' => 'admin@veterinaria.test'],
            ['name' => 'Doctora', 'password' => 'password'],
        );

        Owner::factory(12)->create()->each(function (Owner $owner) {
            Patient::factory(fake()->numberBetween(1, 3))->for($owner)->create()->each(function (Patient $patient) use ($owner) {
                // Visitas pasadas con historial, vacunas y cobro.
                Appointment::factory(fake()->numberBetween(1, 2))->completed()->for($patient)->create()->each(
                    function (Appointment $appointment) use ($patient, $owner) {
                        MedicalRecord::factory()->for($patient)->create([
                            'appointment_id' => $appointment->id,
                            'date' => $appointment->scheduled_at->toDateString(),
                        ]);

                        if (in_array($appointment->type, [AppointmentType::Vaccination, AppointmentType::Deworming])) {
                            $factory = Vaccination::factory()->for($patient);
                            ($appointment->type === AppointmentType::Deworming ? $factory->deworming() : $factory)->create([
                                'appointment_id' => $appointment->id,
                                'applied_at' => $appointment->scheduled_at->toDateString(),
                            ]);
                        }

                        Payment::factory()->for($owner)->create([
                            'appointment_id' => $appointment->id,
                            'concept' => $appointment->type->getLabel().' – '.$patient->name,
                        ]);
                    }
                );

                Vaccination::factory()->for($patient)->create();
            });
        });

        // Algunas vacunas por vencer esta semana para ver los recordatorios en el panel.
        Patient::inRandomOrder()->take(4)->get()->each(fn (Patient $patient) => Vaccination::factory()->for($patient)->create([
            'product' => 'Leptospirosis',
            'applied_at' => today()->subYear()->addDays(fake()->numberBetween(1, 6)),
            'next_due_at' => today()->addDays(fake()->numberBetween(1, 6)),
        ]));

        // Visitas de hoy y próximas.
        Patient::inRandomOrder()->take(3)->get()->each(fn (Patient $patient, int $i) => Appointment::factory()->for($patient)->create([
            'scheduled_at' => today()->setTime(9 + $i * 2, 0),
            'status' => AppointmentStatus::Scheduled,
        ]));
        Patient::inRandomOrder()->take(8)->get()->each(fn (Patient $patient) => Appointment::factory()->for($patient)->create());
    }

    /**
     * En producción crea la cuenta de la doctora con VET_ADMIN_EMAIL y VET_ADMIN_PASSWORD (variables de entorno).
     * Si la cuenta ya existe no se toca, así que la contraseña que ella cambie no se pisa en cada deploy.
     */
    private function seedProductionAdmin(): void
    {
        $email = config('vet.admin_email');
        $password = config('vet.admin_password');

        if (blank($email) || blank($password)) {
            $this->command?->warn('VET_ADMIN_EMAIL / VET_ADMIN_PASSWORD no están definidos: no se creó ningún usuario.');

            return;
        }

        User::firstOrCreate(
            ['email' => $email],
            ['name' => config('vet.admin_name'), 'password' => $password],
        );
    }
}
