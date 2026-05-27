<?php

namespace App\Filament\Resources\StorageUnits;

use App\Filament\Resources\StorageUnits\Pages\CreateStorageUnit;
use App\Filament\Resources\StorageUnits\Pages\EditStorageUnit;
use App\Filament\Resources\StorageUnits\Pages\ListStorageUnits;
use App\Filament\Resources\StorageUnits\Schemas\StorageUnitForm;
use App\Filament\Resources\StorageUnits\Tables\StorageUnitsTable;
use App\Models\StorageUnit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class StorageUnitResource extends Resource
{
    protected static ?string $model = StorageUnit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string|UnitEnum|null $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Storage Units';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return StorageUnitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StorageUnitsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStorageUnits::route('/'),
            'create' => CreateStorageUnit::route('/create'),
            'edit' => EditStorageUnit::route('/{record}/edit'),
        ];
    }
}
