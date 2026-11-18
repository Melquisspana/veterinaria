<?php

namespace App\Models;

use App\Enums\VaccinationKind;
use App\Support\WhatsApp;
use Database\Factories\VaccinationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vaccination extends Model
{
    /** @use HasFactory<VaccinationFactory> */
    use HasFactory;

    protected $fillable = [
        'patient_id', 'appointment_id', 'kind', 'product', 'batch', 'applied_at', 'next_due_at', 'reminder_sent_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'kind' => VaccinationKind::class,
            'applied_at' => 'date',
            'next_due_at' => 'date',
            'reminder_sent_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Próximas dosis que vencen entre hoy y dentro de $days días (incluye vencidas recientes si $includeOverdue),
     * solo la más reciente por mascota y producto, y de mascotas vivas.
     */
    public function scopeDueWithin(Builder $query, int $days, bool $includeOverdue = false): Builder
    {
        return $query
            ->whereNotNull('next_due_at')
            ->when(
                $includeOverdue,
                fn (Builder $q) => $q->whereDate('next_due_at', '<=', today()->addDays($days)),
                fn (Builder $q) => $q->whereDate('next_due_at', '>=', today())->whereDate('next_due_at', '<=', today()->addDays($days)),
            )
            ->whereHas('patient', fn (Builder $q) => $q->where('deceased', false))
            ->whereNotExists(function ($sub) {
                // Si ya se aplicó una dosis más nueva del mismo producto, esta ya no está pendiente.
                $sub->selectRaw('1')
                    ->from('vaccinations as newer')
                    ->whereColumn('newer.patient_id', 'vaccinations.patient_id')
                    ->whereColumn('newer.product', 'vaccinations.product')
                    ->whereColumn('newer.applied_at', '>', 'vaccinations.applied_at')
                    ->whereColumn('newer.id', '!=', 'vaccinations.id');
            });
    }

    public function reminderMessage(): string
    {
        $owner = $this->patient->owner;
        $what = $this->kind === VaccinationKind::Deworming ? 'la desparasitación' : "la vacuna {$this->product}";

        return "Hola {$owner->name}, a {$this->patient->name} le toca {$what} "
            ."el {$this->next_due_at->translatedFormat('j \\d\\e F')}. ¿Le agendo una visita?";
    }

    public function whatsappLink(): ?string
    {
        return WhatsApp::link($this->patient?->owner?->whatsappNumber(), $this->reminderMessage());
    }
}
