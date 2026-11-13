<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Species: string implements HasLabel
{
    case Dog = 'dog';
    case Cat = 'cat';
    case Bird = 'bird';
    case Rabbit = 'rabbit';
    case Rodent = 'rodent';
    case Reptile = 'reptile';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Dog => 'Perro',
            self::Cat => 'Gato',
            self::Bird => 'Ave',
            self::Rabbit => 'Conejo',
            self::Rodent => 'Roedor',
            self::Reptile => 'Reptil',
            self::Other => 'Otro',
        };
    }
}
