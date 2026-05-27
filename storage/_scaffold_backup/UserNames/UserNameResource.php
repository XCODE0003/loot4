<?php

namespace App\Filament\Resources\UserNames;

use App\Filament\Resources\UserNames\Pages\CreateUserName;
use App\Filament\Resources\UserNames\Pages\EditUserName;
use App\Filament\Resources\UserNames\Pages\ListUserNames;
use App\Filament\Resources\UserNames\Schemas\UserNameForm;
use App\Filament\Resources\UserNames\Tables\UserNamesTable;
use App\Models\UserName;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserNameResource extends Resource
{
    protected static ?string $model = UserName::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return UserNameForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserNamesTable::configure($table);
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
            'index' => ListUserNames::route('/'),
            'create' => CreateUserName::route('/create'),
            'edit' => EditUserName::route('/{record}/edit'),
        ];
    }
}
