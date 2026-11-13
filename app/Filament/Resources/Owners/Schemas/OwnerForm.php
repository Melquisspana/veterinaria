<?php

namespace App\Filament\Resources\Owners\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OwnerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::fields());
    }

    /**
     * Campos reutilizados también en el "crear dueño" rápido desde el formulario de mascota.
     *
     * @return array<int, Component>
     */
    public static function fields(): array
    {
        return [
            Section::make('Contacto')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre completo')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    TextInput::make('phone')
                        ->label('Teléfono')
                        ->tel()
                        ->maxLength(30),
                    TextInput::make('whatsapp')
                        ->label('WhatsApp')
                        ->tel()
                        ->maxLength(30)
                        ->helperText('Déjalo vacío si es el mismo teléfono.'),
                    TextInput::make('email')
                        ->label('Correo electrónico')
                        ->email()
                        ->maxLength(255)
                        ->helperText('Para enviarle recordatorios automáticos.')
                        ->columnSpanFull(),
                ]),
            Section::make('Dirección de visita')
                ->columns(2)
                ->schema([
                    TextInput::make('address')
                        ->label('Dirección')
                        ->maxLength(255)
                        ->columnSpanFull(),
                    TextInput::make('address_reference')
                        ->label('Referencia')
                        ->placeholder('Ej.: casa azul, portón negro')
                        ->maxLength(255),
                    TextInput::make('maps_url')
                        ->label('Enlace de Google Maps')
                        ->url()
                        ->maxLength(500)
                        ->helperText('Opcional: pega la ubicación compartida.'),
                ]),
            Textarea::make('notes')
                ->label('Notas')
                ->rows(3)
                ->columnSpanFull(),
        ];
    }
}
