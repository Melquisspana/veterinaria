<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Enums\PaymentStatus;
use App\Enums\VaccinationKind;
use App\Support\WhatsApp;
use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory;

    protected $fillable = [
        'patient_id', 'scheduled_at', 'duration_minutes', 'type', 'status', 'address', 'notes', 'reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
            'type' => AppointmentType::class,
            'status' => AppointmentStatus::class,
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class);
    }

    public function vaccinations(): HasMany
    {
        return $this->hasMany(Vaccination::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', AppointmentStatus::Scheduled);
    }

    public function scopeOnDate(Builder $query, Carbon $date): Builder
    {
        return $query->whereBetween('scheduled_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()]);
    }

    public function endsAt(): Carbon
    {
        return $this->scheduled_at->copy()->addMinutes($this->duration_minutes ?: 60);
    }

    /** Dirección de la visita: la propia o, si no tiene, la del dueño. */
    public function effectiveAddress(): ?string
    {
        return $this->address ?: $this->patient?->owner?->address;
    }

    public function mapsLink(): ?string
    {
        return $this->patient?->owner?->mapsLink($this->address ?: null);
    }

    public function reminderMessage(): string
    {
        $owner = $this->patient->owner;

        return "Hola {$owner->name}, le recuerdo la visita de {$this->type->getLabel()} para {$this->patient->name} "
            ."el {$this->scheduled_at->translatedFormat('l j \\d\\e F')} a las {$this->scheduled_at->format('g:i A')}. "
            .'¡Nos vemos!';
    }

    public function whatsappLink(): ?string
    {
        return WhatsApp::link($this->patient?->owner?->whatsappNumber(), $this->reminderMessage());
    }

    /**
     * Marca la visita como realizada y registra en un solo paso la consulta, las vacunas aplicadas y el cobro.
     *
     * @param  array{reason: string, weight_kg?: float|string|null, temperature_c?: float|string|null, findings?: string|null, diagnosis?: string|null, treatment?: string|null}  $medicalRecord
     * @param  array<int, array{kind: string|VaccinationKind, product: string, batch?: string|null, next_due_at?: string|null}>  $vaccinations
     * @param  array{amount?: float|string|null, method?: string|null, paid?: bool}|null  $payment
     */
    public function complete(array $medicalRecord, array $vaccinations = [], ?array $payment = null): void
    {
        DB::transaction(function () use ($medicalRecord, $vaccinations, $payment): void {
            $date = $this->scheduled_at->toDateString();

            $this->medicalRecord()->updateOrCreate([], [
                ...$medicalRecord,
                'patient_id' => $this->patient_id,
                'date' => $date,
            ]);

            foreach ($vaccinations as $vaccination) {
                $this->vaccinations()->create([
                    ...$vaccination,
                    'patient_id' => $this->patient_id,
                    'applied_at' => $date,
                ]);
            }

            if (filled($payment['amount'] ?? null)) {
                $paid = (bool) ($payment['paid'] ?? false);

                $this->payment()->updateOrCreate([], [
                    'owner_id' => $this->patient->owner_id,
                    'concept' => $this->type->getLabel().' – '.$this->patient->name,
                    'amount' => $payment['amount'],
                    'method' => $payment['method'] ?? null,
                    'status' => $paid ? PaymentStatus::Paid : PaymentStatus::Pending,
                    'paid_at' => $paid ? $date : null,
                ]);
            }

            if (filled($medicalRecord['weight_kg'] ?? null)) {
                $this->patient->update(['weight_kg' => $medicalRecord['weight_kg']]);
            }

            $this->update(['status' => AppointmentStatus::Completed]);
        });
    }
}
