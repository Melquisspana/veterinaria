<?php

namespace Database\Factories;

use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MedicalRecord> */
class MedicalRecordFactory extends Factory
{
    public function definition(): array
    {
        $case = $this->faker->randomElement([
            ['Vómito y decaimiento', 'Abdomen sensible a la palpación', 'Gastroenteritis leve', 'Dieta blanda 3 días, metoclopramida'],
            ['Rascado de oídos', 'Cerumen oscuro en ambos oídos', 'Otitis externa', 'Limpieza ótica y gotas cada 12 h por 7 días'],
            ['Chequeo general', 'Sin hallazgos relevantes', 'Paciente sano', 'Continuar plan de vacunas'],
            ['Cojera pata trasera', 'Dolor a la flexión de rodilla', 'Esguince', 'Reposo y antiinflamatorio 5 días'],
        ]);

        return [
            'patient_id' => Patient::factory(),
            'date' => $this->faker->dateTimeBetween('-6 months'),
            'reason' => $case[0],
            'weight_kg' => $this->faker->randomFloat(1, 1, 35),
            'temperature_c' => $this->faker->randomFloat(1, 37.5, 39.5),
            'findings' => $case[1],
            'diagnosis' => $case[2],
            'treatment' => $case[3],
        ];
    }
}
