<?php

namespace Database\Factories;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Appointment> */
class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'scheduled_at' => now()->addDays($this->faker->numberBetween(1, 20))->setTime($this->faker->numberBetween(8, 17), $this->faker->randomElement([0, 30])),
            'duration_minutes' => 60,
            'type' => $this->faker->randomElement(AppointmentType::cases()),
            'status' => AppointmentStatus::Scheduled,
            'notes' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'scheduled_at' => now()->subDays($this->faker->numberBetween(1, 120))->setTime($this->faker->numberBetween(8, 17), 0),
            'status' => AppointmentStatus::Completed,
        ]);
    }
}
