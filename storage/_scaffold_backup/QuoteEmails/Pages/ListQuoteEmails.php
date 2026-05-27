<?php

namespace App\Filament\Resources\QuoteEmails\Pages;

use App\Filament\Resources\QuoteEmails\QuoteEmailResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQuoteEmails extends ListRecords
{
    protected static string $resource = QuoteEmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
