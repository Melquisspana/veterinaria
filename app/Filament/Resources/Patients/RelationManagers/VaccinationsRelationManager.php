<?php

namespace App\Filament\Resources\Patients\RelationManagers;

use App\Filament\Resources\Patients\Schemas\VaccinationForm;
use App\Models\Vaccination;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VaccinationsRelationManager extends RelationManager
{
    protected static string $relationship = 'vaccinations';

    protected static ?string $title = 'Vacunas y desparasitaciones';

    protected static ?string $modelLabel = 'aplicación';

    protected static ?string $pluralModelLabel = 'aplicaciones';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components(VaccinationForm::fields());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product')
            ->defaultSort('applied_at', 'desc')
            ->columns([
                TextColumn::make('applied_at')->label('Aplicada')->date('d/m/Y')->sortable(),
                TextColumn::make('kind')->label('Tipo')->badge(),
                TextColumn::make('product')->label('Producto')->searchable(),
                TextColumn::make('batch')->label('Lote')->placeholder('—')->toggleable(),
                TextColumn::make('next_due_at')
                    ->label('Próxima dosis')
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->sortable()
                    ->color(fn (Vaccination $record): ?string => match (true) {
                        $record->next_due_at === null => null,
                        $record->next_due_at->isPast() => 'danger',
                        $record->next_due_at->lte(today()->addDays(config('vet.vaccine_reminder_days'))) => 'warning',
                        default => null,
                    }),
            ])
            ->headerActions([
                CreateAction::make()->label('Registrar aplicación'),
            ])
            ->recordActions([
                Action::make('whatsapp')
                    ->label('Recordar')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->url(fn (Vaccination $record): ?string => $record->whatsappLink(), shouldOpenInNewTab: true)
                    ->visible(fn (Vaccination $record): bool => $record->next_due_at !== null && filled($record->whatsappLink())),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
