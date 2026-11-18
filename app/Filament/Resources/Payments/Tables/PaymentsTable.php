<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PaymentsTable
{
    public static function configure(Table $table, bool $showOwner = true): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('owner'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('owner.name')
                    ->label('Dueño')
                    ->searchable()
                    ->visible($showOwner),
                TextColumn::make('concept')
                    ->label('Concepto')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('amount')
                    ->label('Monto')
                    ->money(config('vet.currency'))
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')->money(config('vet.currency'))),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                TextColumn::make('method')
                    ->label('Método')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('paid_at')
                    ->label('Pagado el')
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Estado')->options(PaymentStatus::class),
                SelectFilter::make('method')->label('Método')->options(PaymentMethod::class),
                Filter::make('paid_between')
                    ->label('Fecha de pago')
                    ->schema([
                        DatePicker::make('from')->label('Desde')->native(false),
                        DatePicker::make('until')->label('Hasta')->native(false),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn (Builder $q, string $date) => $q->whereDate('paid_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, string $date) => $q->whereDate('paid_at', '<=', $date))),
            ])
            ->recordActions([
                self::markAsPaidAction(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkAction::make('markAsPaid')
                    ->label('Marcar como pagados')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->action(fn (Collection $records) => $records->each(
                        fn (Payment $payment) => $payment->update(['status' => PaymentStatus::Paid]),
                    )),
            ]);
    }

    public static function markAsPaidAction(): Action
    {
        return Action::make('markAsPaid')
            ->label('Cobrado')
            ->icon('heroicon-o-check')
            ->color('success')
            ->visible(fn (Payment $record): bool => $record->status === PaymentStatus::Pending)
            ->schema([
                Select::make('method')
                    ->label('Método de pago')
                    ->options(PaymentMethod::class)
                    ->default(PaymentMethod::Cash->value)
                    ->required()
                    ->native(false),
            ])
            ->action(fn (Payment $record, array $data) => $record->update([
                'status' => PaymentStatus::Paid,
                'method' => $data['method'],
                'paid_at' => today(),
            ]));
    }
}
