<?php

namespace App\Models;

use App\Enums\Sex;
use App\Enums\Species;
use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Patient extends Model
{
    /** @use HasFactory<PatientFactory> */
    use HasFactory;

    protected $fillable = [
        'owner_id', 'name', 'species', 'breed', 'sex', 'birth_date', 'color',
        'weight_kg', 'sterilized', 'photo', 'deceased', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'species' => Species::class,
            'sex' => Sex::class,
            'birth_date' => 'date',
            'weight_kg' => 'decimal:2',
            'sterilized' => 'boolean',
            'deceased' => 'boolean',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function vaccinations(): HasMany
    {
        return $this->hasMany(Vaccination::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function ageForHumans(): ?string
    {
        if (! $this->birth_date) {
            return null;
        }

        $diff = $this->birth_date->diff(now());

        if ($diff->y > 0) {
            return $diff->y.' '.($diff->y === 1 ? 'año' : 'años').($diff->m ? ' y '.$diff->m.' '.($diff->m === 1 ? 'mes' : 'meses') : '');
        }

        return max($diff->m, 0).' '.($diff->m === 1 ? 'mes' : 'meses');
    }

    /** "Firulais (Perro) – Juan Pérez" para selects y títulos. */
    public function displayName(): string
    {
        return "{$this->name} ({$this->species?->getLabel()}) – {$this->owner?->name}";
    }
}
