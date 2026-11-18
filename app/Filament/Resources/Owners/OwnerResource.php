<?php

namespace App\Filament\Resources\Owners;

use App\Filament\Resources\Owners\Pages\CreateOwner;
use App\Filament\Resources\Owners\Pages\EditOwner;
use App\Filament\Resources\Owners\Pages\ListOwners;
use App\Filament\Resources\Owners\Pages\ViewOwner;
use App\Filament\Resources\Owners\RelationManagers\PatientsRelationManager;
use App\Filament\Resources\Owners\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\Owners\Schemas\OwnerForm;
use App\Filament\Resources\Owners\Schemas\OwnerInfolist;
use App\Filament\Resources\Owners\Tables\OwnersTable;
use App\Models\Owner;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class OwnerResource extends Resource
{
    protected static ?string $model = Owner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Pacientes';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'duenos';

    protected static ?string $modelLabel = 'dueño';

    protected static ?string $pluralModelLabel = 'dueños';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return OwnerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OwnerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OwnersTable::configure($table);
    }

    /** @return array<int, string> */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'phone', 'whatsapp', 'email'];
    }

    /** @return array<string, string|null> */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return ['Teléfono' => $record->phone];
    }

    public static function getRelations(): array
    {
        return [
            PatientsRelationManager::class,
            PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOwners::route('/'),
            'create' => CreateOwner::route('/create'),
            'view' => ViewOwner::route('/{record}'),
            'edit' => EditOwner::route('/{record}/edit'),
        ];
    }
}
