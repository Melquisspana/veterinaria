<?php

namespace App\Filament\Resources\Patients\RelationManagers;

use App\Filament\Resources\Patients\Schemas\MedicalRecordForm;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MedicalRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'medicalRecords';

    protected static ?string $title = 'Historial médico';

    protected static ?string $modelLabel = 'consulta';

    protected static ?string $pluralModelLabel = 'consultas';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components(MedicalRecordForm::fields());
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Grid::make(['default' => 2, 'md' => 4])->schema([
                TextEntry::make('date')->label('Fecha')->date('d/m/Y'),
                TextEntry::make('reason')->label('Motivo')->columnSpan(['default' => 2, 'md' => 1]),
                TextEntry::make('weight_kg')->label('Peso')->suffix(' kg')->placeholder('—'),
                TextEntry::make('temperature_c')->label('Temperatura')->suffix(' °C')->placeholder('—'),
            ]),
            TextEntry::make('findings')->label('Examen / hallazgos')->placeholder('—'),
            TextEntry::make('diagnosis')->label('Diagnóstico')->placeholder('—'),
            TextEntry::make('treatment')->label('Tratamiento')->placeholder('—'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reason')
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('date')->label('Fecha')->date('d/m/Y')->sortable(),
                TextColumn::make('reason')->label('Motivo')->searchable()->wrap(),
                TextColumn::make('diagnosis')->label('Diagnóstico')->limit(60)->wrap()->placeholder('—'),
                TextColumn::make('weight_kg')->label('Peso')->suffix(' kg')->placeholder('—')->toggleable(),
            ])
            ->headerActions([
                CreateAction::make()->label('Nueva consulta'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
