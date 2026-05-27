<?php

namespace App\Filament\Resources\CurrencyTitles;

use App\Filament\Resources\CurrencyTitles\Pages\CreateCurrencyTitle;
use App\Filament\Resources\CurrencyTitles\Pages\EditCurrencyTitle;
use App\Filament\Resources\CurrencyTitles\Pages\ListCurrencyTitles;
use App\Filament\Resources\CurrencyTitles\Schemas\CurrencyTitleForm;
use App\Filament\Resources\CurrencyTitles\Tables\CurrencyTitlesTable;
use App\Models\CurrencyTitle;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CurrencyTitleResource extends Resource
{
    protected static ?string $model = CurrencyTitle::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return CurrencyTitleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CurrencyTitlesTable::configure($table);
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
            'index' => ListCurrencyTitles::route('/'),
            'create' => CreateCurrencyTitle::route('/create'),
            'edit' => EditCurrencyTitle::route('/{record}/edit'),
        ];
    }
}
