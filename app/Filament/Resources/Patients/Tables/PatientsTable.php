<?php

namespace App\Filament\Resources\Patients\Tables;

use App\Enums\Species;
use App\Models\Patient;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PatientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('owner'))
            ->defaultSort('name')
            ->columns([
                ImageColumn::make('photo')
                    ->label('')
                    ->disk(config('vet.uploads_disk'))
                    ->circular()
                    ->defaultImageUrl(asset('images/pet-placeholder.svg')),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Patient $record): ?string => $record->breed),
                TextColumn::make('species')
                    ->label('Especie')
                    ->badge(),
                TextColumn::make('owner.name')
                    ->label('Dueño')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('age')
                    ->label('Edad')
                    ->state(fn (Patient $record): ?string => $record->ageForHumans())
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('species')
                    ->label('Especie')
                    ->options(Species::class),
                TernaryFilter::make('deceased')
                    ->label('Fallecidos')
                    ->placeholder('Solo activos')
                    ->trueLabel('Solo fallecidos')
                    ->falseLabel('Todos')
                    ->queries(
                        true: fn (Builder $query) => $query->where('deceased', true),
                        false: fn (Builder $query) => $query,
                        blank: fn (Builder $query) => $query->where('deceased', false),
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
