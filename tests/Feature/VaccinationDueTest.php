<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\Vaccination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VaccinationDueTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_doses_due_within_the_window(): void
    {
        $patient = Patient::factory()->create();

        $dueSoon = Vaccination::factory()->for($patient)->create(['product' => 'Antirrábica', 'applied_at' => today()->subYear(), 'next_due_at' => today()->addDays(3)]);
        Vaccination::factory()->for($patient)->create(['product' => 'Séxtuple', 'applied_at' => today()->subMonths(2), 'next_due_at' => today()->addDays(30)]);
        $overdue = Vaccination::factory()->for($patient)->create(['product' => 'Drontal', 'applied_at' => today()->subMonths(4), 'next_due_at' => today()->subDays(5)]);

        $this->assertEquals([$dueSoon->id], Vaccination::dueWithin(7)->pluck('id')->all());
        $this->assertEqualsCanonicalizing([$dueSoon->id, $overdue->id], Vaccination::dueWithin(7, includeOverdue: true)->pluck('id')->all());
    }

    public function test_a_newer_dose_of_the_same_product_supersedes_the_old_one(): void
    {
        $patient = Patient::factory()->create();

        Vaccination::factory()->for($patient)->create(['product' => 'Antirrábica', 'applied_at' => today()->subYear(), 'next_due_at' => today()->addDays(2)]);
        Vaccination::factory()->for($patient)->create(['product' => 'Antirrábica', 'applied_at' => today()->subDay(), 'next_due_at' => today()->addYear()]);

        $this->assertSame(0, Vaccination::dueWithin(7)->count());
    }

    public function test_deceased_patients_are_excluded(): void
    {
        $patient = Patient::factory()->create(['deceased' => true]);
        Vaccination::factory()->for($patient)->create(['applied_at' => today()->subYear(), 'next_due_at' => today()->addDay()]);

        $this->assertSame(0, Vaccination::dueWithin(7)->count());
    }
}
