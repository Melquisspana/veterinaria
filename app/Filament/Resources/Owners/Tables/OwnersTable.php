<?php

namespace App\Filament\Resources\Owners\Tables;

use App\Enums\PaymentStatus;
use App\Models\Owner;
use App\Support\WhatsApp;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OwnersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withCount('patients')
                ->withSum(['payments as pending_amount' => fn (Builder $q) => $q->where('status', PaymentStatus::Pending)], 'amount'))
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Owner $record): ?string => $record->address),
                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('patients_count')
                    ->label('Mascotas')
                    ->badge()
                    ->sortable(),
                TextColumn::make('pending_amount')
                    ->label('Debe')
                    ->money(config('vet.currency'))
                    ->placeholder('—')
                    ->color('danger')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('with_debt')
                    ->label('Con saldo pendiente')
                    ->query(fn (Builder $query) => $query->whereHas('payments', fn (Builder $q) => $q->where('status', PaymentStatus::Pending))),
            ])
            ->recordActions([
                Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->iconButton()
                    ->url(fn (Owner $record): ?string => WhatsApp::link($record->whatsappNumber(), "Hola {$record->name}, "), shouldOpenInNewTab: true)
                    ->visible(fn (Owner $record): bool => filled($record->whatsappNumber())),
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
