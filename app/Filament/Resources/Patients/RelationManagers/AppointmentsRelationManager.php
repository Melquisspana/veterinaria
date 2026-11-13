<?php

namespace App\Filament\Resources\Patients\RelationManagers;

use App\Filament\Resources\Appointments\Schemas\AppointmentForm;
use App\Filament\Resources\Appointments\Tables\AppointmentsTable;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class AppointmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'appointments';

    protected static ?string $title = 'Visitas';

    protected static ?string $modelLabel = 'visita';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components(AppointmentForm::fields(withPatient: false));
    }

    public function table(Table $table): Table
    {
        return AppointmentsTable::configure($table, showPatient: false)
            ->headerActions([
                CreateAction::make()->label('Agendar visita'),
            ]);
    }
}
