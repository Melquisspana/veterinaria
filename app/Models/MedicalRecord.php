<?php

namespace App\Models;

use Database\Factories\MedicalRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class MedicalRecord extends Model
{
    /** @use HasFactory<MedicalRecordFactory> */
    use HasFactory;

    protected $fillable = [
        'patient_id', 'appointment_id', 'date', 'reason', 'weight_kg', 'temperature_c',
        'findings', 'diagnosis', 'treatment',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'weight_kg' => 'decimal:2',
            'temperature_c' => 'decimal:1',
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

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
