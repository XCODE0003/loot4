<?php

namespace App\Filament\Resources\QuoteEmails;

use App\Filament\Resources\QuoteEmails\Pages\CreateQuoteEmail;
use App\Filament\Resources\QuoteEmails\Pages\EditQuoteEmail;
use App\Filament\Resources\QuoteEmails\Pages\ListQuoteEmails;
use App\Filament\Resources\QuoteEmails\Schemas\QuoteEmailForm;
use App\Filament\Resources\QuoteEmails\Tables\QuoteEmailsTable;
use App\Models\QuoteEmail;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class QuoteEmailResource extends Resource
{
    protected static ?string $model = QuoteEmail::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return QuoteEmailForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QuoteEmailsTable::configure($table);
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
            'index' => ListQuoteEmails::route('/'),
            'create' => CreateQuoteEmail::route('/create'),
            'edit' => EditQuoteEmail::route('/{record}/edit'),
        ];
    }
}
