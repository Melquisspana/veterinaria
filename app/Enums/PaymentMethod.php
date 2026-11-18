<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasLabel
{
    case Cash = 'cash';
    case Transfer = 'transfer';
    case Card = 'card';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Cash => 'Efectivo',
            self::Transfer => 'Transferencia',
            self::Card => 'Tarjeta',
            self::Other => 'Otro',
        };
    }
}
