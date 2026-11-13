<?php

namespace Database\Factories;

use App\Enums\VaccinationKind;
use App\Models\Patient;
use App\Models\Vaccination;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Vaccination> */
class VaccinationFactory extends Factory
{
    public function definition(): array
    {
        $applied = $this->faker->dateTimeBetween('-11 months', '-1 week');

        return [
            'patient_id' => Patient::factory(),
            'kind' => VaccinationKind::Vaccine,
            'product' => $this->faker->randomElement(['Antirrábica', 'Séxtuple', 'Triple felina', 'Parvovirus']),
            'batch' => strtoupper($this->faker->bothify('??####')),
            'applied_at' => $applied,
            'next_due_at' => (clone $applied)->modify('+1 year'),
        ];
    }

    public function deworming(): static
    {
        return $this->state(fn (array $attributes) => [
            'kind' => VaccinationKind::Deworming,
            'product' => $this->faker->randomElement(['Drontal', 'NexGard', 'Bravecto']),
            'next_due_at' => (clone $attributes['applied_at'])->modify('+3 months'),
        ]);
    }
}
