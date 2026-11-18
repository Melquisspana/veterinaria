<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Enums\PaymentStatus;
use App\Enums\VaccinationKind;
use App\Models\Appointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_completing_a_visit_records_history_vaccines_and_payment(): void
    {
        $appointment = Appointment::factory()->create([
            'type' => AppointmentType::Vaccination,
            'scheduled_at' => now()->setTime(10, 0),
        ]);

        $appointment->complete(
            medicalRecord: ['reason' => 'Vacunación anual', 'weight_kg' => 12.5, 'temperature_c' => 38.6, 'diagnosis' => 'Sano'],
            vaccinations: [['kind' => VaccinationKind::Vaccine->value, 'product' => 'Antirrábica', 'batch' => 'AB123', 'next_due_at' => now()->addYear()->toDateString()]],
            payment: ['amount' => 25, 'method' => 'cash', 'paid' => true],
        );

        $appointment->refresh();

        $this->assertSame(AppointmentStatus::Completed, $appointment->status);
        $this->assertSame('Vacunación anual', $appointment->medicalRecord->reason);
        $this->assertTrue($appointment->medicalRecord->date->isSameDay($appointment->scheduled_at));

        $vaccination = $appointment->vaccinations->sole();
        $this->assertSame('Antirrábica', $vaccination->product);
        $this->assertSame($appointment->patient_id, $vaccination->patient_id);
        $this->assertTrue($vaccination->applied_at->isSameDay($appointment->scheduled_at));

        $this->assertSame(PaymentStatus::Paid, $appointment->payment->status);
        $this->assertSame('25.00', $appointment->payment->amount);
        $this->assertSame($appointment->patient->owner_id, $appointment->payment->owner_id);
        $this->assertNotNull($appointment->payment->paid_at);

        $this->assertSame('12.50', $appointment->patient->fresh()->weight_kg);
    }

    public function test_completing_without_amount_creates_no_payment_and_pending_when_unpaid(): void
    {
        $withoutAmount = Appointment::factory()->create();
        $withoutAmount->complete(['reason' => 'Control'], [], ['amount' => null, 'paid' => true]);
        $this->assertNull($withoutAmount->fresh()->payment);

        $unpaid = Appointment::factory()->create();
        $unpaid->complete(['reason' => 'Consulta'], [], ['amount' => 30, 'paid' => false]);
        $this->assertSame(PaymentStatus::Pending, $unpaid->fresh()->payment->status);
        $this->assertNull($unpaid->fresh()->payment->paid_at);
        $this->assertEquals(30, $unpaid->patient->owner->pendingBalance());
    }
}
