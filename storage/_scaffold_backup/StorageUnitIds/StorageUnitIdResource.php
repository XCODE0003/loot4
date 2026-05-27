<?php

namespace App\Filament\Resources\StorageUnitIds;

use App\Filament\Resources\StorageUnitIds\Pages\CreateStorageUnitId;
use App\Filament\Resources\StorageUnitIds\Pages\EditStorageUnitId;
use App\Filament\Resources\StorageUnitIds\Pages\ListStorageUnitIds;
use App\Filament\Resources\StorageUnitIds\Schemas\StorageUnitIdForm;
use App\Filament\Resources\StorageUnitIds\Tables\StorageUnitIdsTable;
use App\Models\StorageUnitId;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StorageUnitIdResource extends Resource
{
    protected static ?string $model = StorageUnitId::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return StorageUnitIdForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StorageUnitIdsTable::configure($table);
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
            'index' => ListStorageUnitIds::route('/'),
            'create' => CreateStorageUnitId::route('/create'),
            'edit' => EditStorageUnitId::route('/{record}/edit'),
        ];
    }
}
