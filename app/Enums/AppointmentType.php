<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AppointmentType: string implements HasColor, HasLabel
{
    case Vaccination = 'vaccination';
    case Consultation = 'consultation';
    case FollowUp = 'follow_up';
    case Deworming = 'deworming';
    case Procedure = 'procedure';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Vaccination => 'Vacunación',
            self::Consultation => 'Consulta',
            self::FollowUp => 'Control',
            self::Deworming => 'Desparasitación',
            self::Procedure => 'Procedimiento',
            self::Other => 'Otro',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Vaccination => 'success',
            self::Consultation => 'info',
            self::FollowUp => 'primary',
            self::Deworming => 'warning',
            self::Procedure => 'danger',
            self::Other => 'gray',
        };
    }
}
