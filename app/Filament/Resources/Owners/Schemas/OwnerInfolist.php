<?php

namespace App\Filament\Resources\Owners\Schemas;

use App\Models\Owner;
use App\Support\WhatsApp;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OwnerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Contacto')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')->label('Nombre'),
                        TextEntry::make('phone')
                            ->label('Teléfono')
                            ->placeholder('—')
                            ->url(fn (Owner $record): ?string => $record->phone ? 'tel:'.$record->phone : null),
                        TextEntry::make('whatsapp')
                            ->label('WhatsApp')
                            ->state(fn (Owner $record): ?string => $record->whatsappNumber())
                            ->placeholder('—')
                            ->color('success')
                            ->url(fn (Owner $record): ?string => WhatsApp::link($record->whatsappNumber(), "Hola {$record->name}, "), shouldOpenInNewTab: true),
                        TextEntry::make('email')->label('Correo')->placeholder('—'),
                        TextEntry::make('pending_balance')
                            ->label('Saldo pendiente')
                            ->state(fn (Owner $record): float => $record->pendingBalance())
                            ->money(config('vet.currency'))
                            ->color(fn (float $state): string => $state > 0 ? 'danger' : 'success'),
                    ]),
                Section::make('Dirección')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('address')
                            ->label('Dirección')
                            ->placeholder('—')
                            ->url(fn (Owner $record): ?string => $record->mapsLink(), shouldOpenInNewTab: true)
                            ->icon('heroicon-o-map-pin'),
                        TextEntry::make('address_reference')->label('Referencia')->placeholder('—'),
                        TextEntry::make('notes')->label('Notas')->placeholder('—')->columnSpanFull(),
                    ]),
            ]);
    }
}
