<?php

namespace App\Filament\Resources\Owners\RelationManagers;

use App\Filament\Resources\Patients\PatientResource;
use App\Filament\Resources\Patients\Schemas\PatientForm;
use App\Models\Patient;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PatientsRelationManager extends RelationManager
{
    protected static string $relationship = 'patients';

    protected static ?string $title = 'Mascotas';

    protected static ?string $modelLabel = 'mascota';

    public function form(Schema $schema): Schema
    {
        return $schema->components(PatientForm::fields(withOwner: false));
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('photo')->label('')->disk(config('vet.uploads_disk'))->circular()->defaultImageUrl(asset('images/pet-placeholder.svg')),
                TextColumn::make('name')->label('Nombre')->weight('bold'),
                TextColumn::make('species')->label('Especie')->badge(),
                TextColumn::make('breed')->label('Raza')->placeholder('—'),
                TextColumn::make('age')->label('Edad')->state(fn (Patient $record): ?string => $record->ageForHumans())->placeholder('—'),
            ])
            ->headerActions([
                CreateAction::make()->label('Agregar mascota'),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Ver ficha')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Patient $record): string => PatientResource::getUrl('view', ['record' => $record])),
            ])
            ->recordUrl(fn (Patient $record): string => PatientResource::getUrl('view', ['record' => $record]));
    }
}
