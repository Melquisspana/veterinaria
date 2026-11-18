<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Models\Patient;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::fields());
    }

    /** @return array<int, Component> */
    public static function fields(bool $withPatient = true): array
    {
        return [
            Section::make('Visita')
                ->columns(2)
                ->schema([
                    Select::make('patient_id')
                        ->label('Mascota')
                        ->relationship(
                            'patient',
                            'name',
                            modifyQueryUsing: fn (Builder $query) => $query->where('deceased', false)->with('owner'),
                        )
                        ->getOptionLabelFromRecordUsing(fn (Patient $record): string => $record->displayName())
                        ->searchable(['name'])
                        ->preload()
                        ->required()
                        ->live()
                        ->visible($withPatient)
                        ->columnSpanFull(),
                    DateTimePicker::make('scheduled_at')
                        ->label('Fecha y hora')
                        ->required()
                        ->seconds(false)
                        ->minutesStep(15)
                        ->native(false)
                        ->displayFormat('d/m/Y h:i A')
                        ->default(now()->addDay()->setTime(9, 0)),
                    Select::make('duration_minutes')
                        ->label('Duración')
                        ->options([30 => '30 min', 45 => '45 min', 60 => '1 hora', 90 => '1 h 30 min', 120 => '2 horas'])
                        ->default(60)
                        ->required()
                        ->native(false),
                    ToggleButtons::make('type')
                        ->label('Tipo de visita')
                        ->options(AppointmentType::class)
                        ->default(AppointmentType::Consultation)
                        ->required()
                        ->inline()
                        ->columnSpanFull(),
                    ToggleButtons::make('status')
                        ->label('Estado')
                        ->options(AppointmentStatus::class)
                        ->default(AppointmentStatus::Scheduled)
                        ->inline()
                        ->visibleOn('edit')
                        ->columnSpanFull(),
                    TextInput::make('address')
                        ->label('Dirección (si es distinta a la del dueño)')
                        ->placeholder(fn (Get $get): ?string => $get('patient_id') ? Patient::with('owner')->find($get('patient_id'))?->owner?->address : null)
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Textarea::make('notes')
                        ->label('Notas')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),
        ];
    }
}
