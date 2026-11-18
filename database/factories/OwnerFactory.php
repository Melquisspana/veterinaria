<?php

namespace Database\Factories;

use App\Models\Owner;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Owner> */
class OwnerFactory extends Factory
{
    public function definition(): array
    {
        $phone = $this->faker->numerify('########');

        return [
            'name' => $this->faker->name(),
            'phone' => $phone,
            'whatsapp' => $phone,
            'email' => $this->faker->optional(0.7)->safeEmail(),
            'address' => $this->faker->streetAddress().', '.$this->faker->city(),
            'address_reference' => $this->faker->optional()->randomElement([
                'Casa azul con portón negro', 'Frente a la pulpería', 'Apartamento 3B', 'Detrás de la iglesia',
            ]),
            'notes' => null,
        ];
    }
}
