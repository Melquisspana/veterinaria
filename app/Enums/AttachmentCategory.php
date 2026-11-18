<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AttachmentCategory: string implements HasLabel
{
    case Photo = 'photo';
    case Lab = 'lab';
    case Xray = 'xray';
    case Prescription = 'prescription';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Photo => 'Foto',
            self::Lab => 'Laboratorio',
            self::Xray => 'Radiografía / Imagen',
            self::Prescription => 'Receta',
            self::Other => 'Otro',
        };
    }
}
