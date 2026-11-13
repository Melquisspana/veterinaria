<?php

namespace App\Filament\Resources\Patients\Schemas;

use App\Filament\Resources\Owners\OwnerResource;
use App\Models\Patient;
use App\Models\Vaccination;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PatientInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make()
                    ->columnSpan(['default' => 3, 'lg' => 2])
                    ->columns(['default' => 2, 'md' => 3])
                    ->schema([
                        ImageEntry::make('photo')
                            ->hiddenLabel()
                            ->disk(config('vet.uploads_disk'))
                            ->circular()
                            ->imageSize(96)
                            ->defaultImageUrl(asset('images/pet-placeholder.svg'))
                            ->columnSpan(['default' => 2, 'md' => 1]),
                        TextEntry::make('species')->label('Especie')->badge(),
                        TextEntry::make('breed')->label('Raza')->placeholder('—'),
                        TextEntry::make('sex')->label('Sexo')->placeholder('—'),
                        TextEntry::make('age')->label('Edad')->state(fn (Patient $record): ?string => $record->ageForHumans())->placeholder('—'),
                        TextEntry::make('weight_kg')->label('Peso')->suffix(' kg')->placeholder('—'),
                        TextEntry::make('color')->label('Color')->placeholder('—'),
                        IconEntry::make('sterilized')->label('Esterilizado')->boolean(),
                        TextEntry::make('deceased')
                            ->label('Estado')
                            ->state(fn (Patient $record): string => $record->deceased ? 'Fallecido' : 'Activo')
                            ->badge()
                            ->color(fn (Patient $record): string => $record->deceased ? 'gray' : 'success'),
                        TextEntry::make('notes')->label('Notas')->placeholder('—')->columnSpanFull(),
                    ]),
                Grid::make(1)
                    ->columnSpan(['default' => 3, 'lg' => 1])
                    ->schema([
                        Section::make('Dueño')
                            ->schema([
                                TextEntry::make('owner.name')
                                    ->hiddenLabel()
                                    ->weight('bold')
                                    ->url(fn (Patient $record): string => OwnerResource::getUrl('view', ['record' => $record->owner_id])),
                                TextEntry::make('owner.phone')
                                    ->hiddenLabel()
                                    ->icon('heroicon-o-phone')
                                    ->placeholder('Sin teléfono')
                                    ->url(fn (Patient $record): ?string => $record->owner->phone ? 'tel:'.$record->owner->phone : null),
                                TextEntry::make('owner.address')
                                    ->hiddenLabel()
                                    ->icon('heroicon-o-map-pin')
                                    ->placeholder('Sin dirección')
                                    ->url(fn (Patient $record): ?string => $record->owner->mapsLink(), shouldOpenInNewTab: true),
                            ]),
                        Section::make('Próximas dosis')
                            ->schema([
                                TextEntry::make('next_doses')
                                    ->hiddenLabel()
                                    ->listWithLineBreaks()
                                    ->placeholder('Nada pendiente')
                                    ->state(fn (Patient $record): array => Vaccination::query()
                                        ->whereBelongsTo($record)
                                        ->dueWithin(365, includeOverdue: true)
                                        ->orderBy('next_due_at')
                                        ->get()
                                        ->map(fn (Vaccination $vaccination): string => $vaccination->product.': '.$vaccination->next_due_at->format('d/m/Y')
                                            .($vaccination->next_due_at->isPast() ? ' (vencida)' : ''))
                                        ->all()),
                            ]),
                    ]),
            ]);
    }
}
