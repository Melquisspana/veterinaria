<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Appointment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::fields());
    }

    /** @return array<int, Component> */
    public static function fields(bool $withOwner = true): array
    {
        return [
            Section::make('Cobro')
                ->columns(2)
                ->schema([
                    Select::make('owner_id')
                        ->label('Dueño')
                        ->relationship('owner', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->visible($withOwner),
                    Select::make('appointment_id')
                        ->label('Visita (opcional)')
                        ->relationship(
                            'appointment',
                            'id',
                            modifyQueryUsing: fn (Builder $query, Get $get, $livewire) => $query
                                ->with('patient')
                                ->whereHas('patient', fn (Builder $q) => $q->where(
                                    'owner_id',
                                    $livewire instanceof RelationManager ? $livewire->getOwnerRecord()->getKey() : $get('owner_id'),
                                ))
                                ->latest('scheduled_at'),
                        )
                        ->getOptionLabelFromRecordUsing(fn (Appointment $record): string => $record->scheduled_at->format('d/m/Y').' – '.$record->patient->name.' ('.$record->type->getLabel().')')
                        ->searchable()
                        ->preload(),
                    TextInput::make('concept')
                        ->label('Concepto')
                        ->required()
                        ->maxLength(255)
                        ->datalist(['Consulta a domicilio', 'Vacuna', 'Desparasitación', 'Control', 'Medicamentos'])
                        ->columnSpanFull(),
                    TextInput::make('amount')
                        ->label('Monto')
                        ->numeric()
                        ->minValue(0)
                        ->prefix('$')
                        ->required(),
                    Select::make('method')
                        ->label('Método de pago')
                        ->options(PaymentMethod::class)
                        ->native(false),
                    ToggleButtons::make('status')
                        ->label('Estado')
                        ->options(PaymentStatus::class)
                        ->default(PaymentStatus::Pending)
                        ->inline()
                        ->required()
                        ->live(),
                    DatePicker::make('paid_at')
                        ->label('Fecha de pago')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->visible(fn (Get $get): bool => in_array($get('status'), [PaymentStatus::Paid, PaymentStatus::Paid->value], true))
                        ->helperText('Si se deja vacío se usa hoy.'),
                    Textarea::make('notes')
                        ->label('Notas')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),
        ];
    }
}
