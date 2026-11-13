<?php

namespace App\Filament\Resources\Patients\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;

class MedicalRecordForm
{
    /** @return array<int, Component> */
    public static function fields(bool $withDate = true): array
    {
        return [
            Grid::make(['default' => 2, 'md' => 4])->schema([
                DatePicker::make('date')
                    ->label('Fecha')
                    ->default(today())
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->visible($withDate),
                TextInput::make('reason')
                    ->label('Motivo de la consulta')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan($withDate ? ['default' => 2, 'md' => 3] : ['default' => 2, 'md' => 2]),
                TextInput::make('weight_kg')
                    ->label('Peso (kg)')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.1),
                TextInput::make('temperature_c')
                    ->label('Temperatura (°C)')
                    ->numeric()
                    ->minValue(30)
                    ->maxValue(45)
                    ->step(0.1),
            ]),
            Textarea::make('findings')->label('Examen / hallazgos')->rows(3),
            Textarea::make('diagnosis')->label('Diagnóstico')->rows(2),
            Textarea::make('treatment')->label('Tratamiento / indicaciones')->rows(3),
        ];
    }
}
