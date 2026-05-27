<?php

namespace App\Filament\Resources\TicketSubjects;

use App\Filament\Resources\TicketSubjects\Pages\CreateTicketSubject;
use App\Filament\Resources\TicketSubjects\Pages\EditTicketSubject;
use App\Filament\Resources\TicketSubjects\Pages\ListTicketSubjects;
use App\Filament\Resources\TicketSubjects\Schemas\TicketSubjectForm;
use App\Filament\Resources\TicketSubjects\Tables\TicketSubjectsTable;
use App\Models\TicketSubject;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TicketSubjectResource extends Resource
{
    protected static ?string $model = TicketSubject::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return TicketSubjectForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TicketSubjectsTable::configure($table);
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
            'index' => ListTicketSubjects::route('/'),
            'create' => CreateTicketSubject::route('/create'),
            'edit' => EditTicketSubject::route('/{record}/edit'),
        ];
    }
}
