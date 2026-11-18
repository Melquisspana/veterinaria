<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum VaccinationKind: string implements HasColor, HasLabel
{
    case Vaccine = 'vaccine';
    case Deworming = 'deworming';

    public function getLabel(): string
    {
        return match ($this) {
            self::Vaccine => 'Vacuna',
            self::Deworming => 'Desparasitación',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Vaccine => 'success',
            self::Deworming => 'warning',
        };
    }
}
