<?php

namespace App\Filament\Resources\Owners\RelationManagers;

use App\Filament\Resources\Payments\Schemas\PaymentForm;
use App\Filament\Resources\Payments\Tables\PaymentsTable;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Cobros';

    protected static ?string $modelLabel = 'cobro';

    public function form(Schema $schema): Schema
    {
        return $schema->components(PaymentForm::fields(withOwner: false));
    }

    public function table(Table $table): Table
    {
        return PaymentsTable::configure($table, showOwner: false)
            ->headerActions([
                CreateAction::make()->label('Registrar cobro'),
            ]);
    }
}
