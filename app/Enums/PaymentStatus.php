<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Paid = 'paid';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Paid => 'Pagado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'danger',
            self::Paid => 'success',
        };
    }
}
