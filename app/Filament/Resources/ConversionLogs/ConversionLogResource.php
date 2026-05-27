<?php

namespace App\Filament\Resources\ConversionLogs;

use App\Filament\Resources\ConversionLogs\Pages\CreateConversionLog;
use App\Filament\Resources\ConversionLogs\Pages\EditConversionLog;
use App\Filament\Resources\ConversionLogs\Pages\ListConversionLogs;
use App\Filament\Resources\ConversionLogs\Pages\ViewConversionLog;
use App\Filament\Resources\ConversionLogs\Schemas\ConversionLogForm;
use App\Filament\Resources\ConversionLogs\Schemas\ConversionLogInfolist;
use App\Filament\Resources\ConversionLogs\Tables\ConversionLogsTable;
use App\Models\ConversionLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ConversionLogResource extends Resource
{
    protected static ?string $model = ConversionLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSignal;

    protected static string|UnitEnum|null $navigationGroup = 'Analytics';

    protected static ?string $navigationLabel = 'Conversion Logs';

    protected static ?string $recordTitleAttribute = 'event';

    public static function form(Schema $schema): Schema
    {
        return ConversionLogForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ConversionLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConversionLogsTable::configure($table);
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
            'index' => ListConversionLogs::route('/'),
            'create' => CreateConversionLog::route('/create'),
            'view' => ViewConversionLog::route('/{record}'),
            'edit' => EditConversionLog::route('/{record}/edit'),
        ];
    }
}
