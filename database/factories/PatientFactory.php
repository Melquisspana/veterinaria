<?php

namespace Database\Factories;

use App\Enums\Sex;
use App\Enums\Species;
use App\Models\Owner;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Patient> */
class PatientFactory extends Factory
{
    public function definition(): array
    {
        $species = $this->faker->randomElement([Species::Dog, Species::Dog, Species::Cat, Species::Cat, Species::Rabbit, Species::Bird]);

        $breeds = [
            Species::Dog->value => ['Mestizo', 'Labrador', 'Poodle', 'Chihuahua', 'Pastor Alemán', 'Golden Retriever', 'Schnauzer'],
            Species::Cat->value => ['Mestizo', 'Siamés', 'Persa', 'Angora'],
            Species::Rabbit->value => ['Cabeza de león', 'Mini Lop'],
            Species::Bird->value => ['Perico australiano', 'Cacatúa', 'Canario'],
        ];

        return [
            'owner_id' => Owner::factory(),
            'name' => $this->faker->randomElement(['Luna', 'Max', 'Rocky', 'Toby', 'Lola', 'Michi', 'Simba', 'Nala', 'Coco', 'Bruno', 'Canela', 'Firulais', 'Pelusa', 'Thor', 'Kira']),
            'species' => $species,
            'breed' => $this->faker->randomElement($breeds[$species->value]),
            'sex' => $this->faker->randomElement(Sex::cases()),
            'birth_date' => $this->faker->dateTimeBetween('-12 years', '-3 months'),
            'color' => $this->faker->randomElement(['Negro', 'Blanco', 'Café', 'Atigrado', 'Dorado', 'Gris', 'Manchado']),
            'weight_kg' => $this->faker->randomFloat(1, 0.5, 35),
            'sterilized' => $this->faker->boolean(),
            'deceased' => false,
        ];
    }
}
