<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Owner;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Payment> */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        $paid = $this->faker->boolean(75);

        return [
            'owner_id' => Owner::factory(),
            'concept' => $this->faker->randomElement(['Consulta a domicilio', 'Vacuna antirrábica', 'Desparasitación', 'Control']),
            'amount' => $this->faker->randomElement([15, 20, 25, 30, 40, 50]),
            'method' => $paid ? $this->faker->randomElement(PaymentMethod::cases()) : null,
            'status' => $paid ? PaymentStatus::Paid : PaymentStatus::Pending,
            'paid_at' => $paid ? $this->faker->dateTimeBetween('-2 months') : null,
        ];
    }
}
