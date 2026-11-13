<?php

namespace App\Filament\Resources\Patients\Schemas;

use App\Enums\Sex;
use App\Enums\Species;
use App\Filament\Resources\Owners\Schemas\OwnerForm;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PatientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::fields());
    }

    /** @return array<int, Component> */
    public static function fields(bool $withOwner = true): array
    {
        return [
            Section::make('Datos de la mascota')
                ->columns(2)
                ->schema([
                    Select::make('owner_id')
                        ->label('Dueño')
                        ->relationship('owner', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->createOptionForm(OwnerForm::fields())
                        ->createOptionModalHeading('Nuevo dueño')
                        ->visible($withOwner)
                        ->columnSpanFull(),
                    TextInput::make('name')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(255),
                    Select::make('species')
                        ->label('Especie')
                        ->options(Species::class)
                        ->required()
                        ->native(false),
                    TextInput::make('breed')
                        ->label('Raza')
                        ->maxLength(255),
                    ToggleButtons::make('sex')
                        ->label('Sexo')
                        ->options(Sex::class)
                        ->inline(),
                    DatePicker::make('birth_date')
                        ->label('Fecha de nacimiento (aprox.)')
                        ->maxDate(now())
                        ->native(false)
                        ->displayFormat('d/m/Y'),
                    TextInput::make('color')
                        ->label('Color / señas')
                        ->maxLength(255),
                    TextInput::make('weight_kg')
                        ->label('Peso (kg)')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.1),
                    Toggle::make('sterilized')
                        ->label('Esterilizado/a')
                        ->inline(false),
                ]),
            Section::make('Foto y notas')
                ->columns(2)
                ->schema([
                    FileUpload::make('photo')
                        ->label('Foto')
                        ->image()
                        ->disk(config('vet.uploads_disk'))
                        ->directory('patients')
                        ->imageEditor()
                        ->maxSize(5120),
                    Textarea::make('notes')
                        ->label('Notas (alergias, carácter, etc.)')
                        ->rows(5),
                    Toggle::make('deceased')
                        ->label('Fallecido')
                        ->helperText('Deja de aparecer en recordatorios.')
                        ->visibleOn('edit'),
                ]),
        ];
    }
}
