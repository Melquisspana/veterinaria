<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Database\Factories\OwnerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Owner extends Model
{
    /** @use HasFactory<OwnerFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'phone', 'whatsapp', 'email', 'address', 'address_reference', 'maps_url', 'notes',
    ];

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function pendingBalance(): float
    {
        return (float) $this->payments()->where('status', PaymentStatus::Pending)->sum('amount');
    }

    public function whatsappNumber(): ?string
    {
        return $this->whatsapp ?: $this->phone;
    }

    public function mapsLink(?string $address = null): ?string
    {
        if ($address === null && $this->maps_url) {
            return $this->maps_url;
        }

        $address ??= $this->address;

        return $address ? 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($address) : null;
    }
}
