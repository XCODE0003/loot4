<?php

namespace App\Filament\Resources\CurrencyTitles\Pages;

use App\Filament\Resources\CurrencyTitles\CurrencyTitleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCurrencyTitle extends EditRecord
{
    protected static string $resource = CurrencyTitleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
