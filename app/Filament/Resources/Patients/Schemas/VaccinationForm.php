<?php

namespace App\Filament\Resources\Patients\Schemas;

use App\Enums\VaccinationKind;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Carbon;

class VaccinationForm
{
    /** @var array<int, string> */
    public const SUGGESTED_PRODUCTS = [
        'Antirrábica', 'Séxtuple', 'Óctuple', 'Parvovirus', 'Moquillo', 'Leptospirosis', 'Bordetella (tos de las perreras)',
        'Triple felina', 'Leucemia felina', 'Drontal', 'NexGard', 'Bravecto', 'Simparica', 'Milbemax',
    ];

    /** @return array<int, Component> */
    public static function fields(bool $withAppliedAt = true): array
    {
        return [
            Grid::make(['default' => 1, 'md' => 2])->schema([
                ToggleButtons::make('kind')
                    ->label('Tipo')
                    ->options(VaccinationKind::class)
                    ->default(VaccinationKind::Vaccine)
                    ->inline()
                    ->required(),
                TextInput::make('product')
                    ->label('Producto / vacuna')
                    ->datalist(self::SUGGESTED_PRODUCTS)
                    ->required()
                    ->maxLength(255),
                TextInput::make('batch')
                    ->label('Lote')
                    ->maxLength(100),
                DatePicker::make('applied_at')
                    ->label('Fecha aplicada')
                    ->default(today())
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->live()
                    ->visible($withAppliedAt),
                ToggleButtons::make('repeat_in')
                    ->label('Próxima dosis en')
                    ->options(['1' => '1 mes', '3' => '3 meses', '6' => '6 meses', '12' => '1 año'])
                    ->inline()
                    ->dehydrated(false)
                    ->live()
                    ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                        if (! $state) {
                            return;
                        }

                        $from = $get('applied_at') ? Carbon::parse($get('applied_at')) : today();
                        $set('next_due_at', $from->addMonthsNoOverflow((int) $state)->toDateString());
                    }),
                DatePicker::make('next_due_at')
                    ->label('Fecha próxima dosis')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->helperText('Se usará para los recordatorios.'),
            ]),
        ];
    }
}
