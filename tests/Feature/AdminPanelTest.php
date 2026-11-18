<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Filament\Pages\Calendar;
use App\Filament\Resources\Appointments\AppointmentResource;
use App\Filament\Resources\Appointments\Pages\ListAppointments;
use App\Filament\Resources\Owners\OwnerResource;
use App\Filament\Resources\Owners\Pages\CreateOwner;
use App\Filament\Resources\Patients\PatientResource;
use App\Filament\Resources\Payments\Pages\ListPayments;
use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Appointment;
use App\Models\Owner;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vaccination;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_guests_are_redirected_to_login(): void
    {
        auth()->logout();

        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_every_page_renders(): void
    {
        $appointment = Appointment::factory()->create(['scheduled_at' => today()->setTime(9, 0)]);
        Vaccination::factory()->for($appointment->patient)->create(['applied_at' => today()->subYear(), 'next_due_at' => today()->addDay()]);
        $payment = Payment::factory()->for($appointment->patient->owner)->create();

        $urls = [
            '/admin',
            Calendar::getUrl(),
            OwnerResource::getUrl('index'),
            OwnerResource::getUrl('create'),
            OwnerResource::getUrl('view', ['record' => $appointment->patient->owner]),
            OwnerResource::getUrl('edit', ['record' => $appointment->patient->owner]),
            PatientResource::getUrl('index'),
            PatientResource::getUrl('create'),
            PatientResource::getUrl('view', ['record' => $appointment->patient]),
            PatientResource::getUrl('edit', ['record' => $appointment->patient]),
            AppointmentResource::getUrl('index'),
            AppointmentResource::getUrl('create', ['patient' => $appointment->patient_id, 'type' => 'vaccination']),
            AppointmentResource::getUrl('view', ['record' => $appointment]),
            AppointmentResource::getUrl('edit', ['record' => $appointment]),
            PaymentResource::getUrl('index'),
            PaymentResource::getUrl('create'),
            PaymentResource::getUrl('edit', ['record' => $payment]),
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_an_owner_can_be_created_from_the_panel(): void
    {
        Livewire::test(CreateOwner::class)
            ->fillForm(['name' => 'María López', 'phone' => '99998888', 'address' => 'Col. Centro, casa 12'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Owner::class, ['name' => 'María López', 'phone' => '99998888']);
    }

    public function test_a_visit_can_be_completed_from_the_table(): void
    {
        $appointment = Appointment::factory()->create(['scheduled_at' => today()->addDay()->setTime(9, 0)]);

        Livewire::test(ListAppointments::class)
            ->assertCanSeeTableRecords([$appointment])
            ->callAction(TestAction::make('complete')->table($appointment), [
                'record' => ['reason' => 'Consulta general', 'diagnosis' => 'Sano'],
                'vaccinations' => [['kind' => 'vaccine', 'product' => 'Séxtuple', 'next_due_at' => today()->addYear()->toDateString()]],
                'payment' => ['amount' => 20, 'method' => 'transfer', 'paid' => false],
            ])
            ->assertHasNoFormErrors();

        $appointment->refresh();
        $this->assertSame(AppointmentStatus::Completed, $appointment->status);
        $this->assertSame('Consulta general', $appointment->medicalRecord->reason);
        $this->assertSame('Séxtuple', $appointment->vaccinations->sole()->product);
        $this->assertSame(PaymentStatus::Pending, $appointment->payment->status);
    }

    public function test_a_pending_payment_can_be_marked_as_paid(): void
    {
        $payment = Payment::factory()->create(['status' => PaymentStatus::Pending, 'method' => null, 'paid_at' => null]);

        Livewire::test(ListPayments::class)
            ->callAction(TestAction::make('markAsPaid')->table($payment), ['method' => 'cash']);

        $payment->refresh();
        $this->assertSame(PaymentStatus::Paid, $payment->status);
        $this->assertTrue($payment->paid_at->isToday());
    }

    public function test_patients_can_be_found_with_global_search_by_owner_name(): void
    {
        $patient = Patient::factory()->for(Owner::factory()->state(['name' => 'Rigoberta Menchú']))->create(['name' => 'Canela']);

        $this->assertStringContainsString('Canela', json_encode(PatientResource::getGlobalSearchResults('Rigoberta')->all()));
        $this->assertNotNull($patient);
    }
}
