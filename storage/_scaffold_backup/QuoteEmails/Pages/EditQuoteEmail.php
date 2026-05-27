<?php

namespace App\Filament\Resources\QuoteEmails\Pages;

use App\Filament\Resources\QuoteEmails\QuoteEmailResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditQuoteEmail extends EditRecord
{
    protected static string $resource = QuoteEmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
