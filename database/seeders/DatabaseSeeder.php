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
        User::firstOrCreate(
            ['email' => 'admin@veterinaria.test'],
            ['name' => 'Doctora', 'password' => 'password'],
        );

        if (! app()->environment('local', 'testing')) {
            return; // En producción solo se crea el usuario; los datos de ejemplo son para pruebas.
        }

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
}
