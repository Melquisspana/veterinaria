<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Enums\PaymentStatus;
use App\Filament\Resources\Payments\PaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Registrar cobro'),
        ];
    }

    /** @return array<string, Tab> */
    public function getTabs(): array
    {
        return [
            'pending' => Tab::make('Pendientes')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', PaymentStatus::Pending)),
            'this_month' => Tab::make('Cobrado este mes')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('status', PaymentStatus::Paid)
                    ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])),
            'all' => Tab::make('Todos'),
        ];
    }
}
