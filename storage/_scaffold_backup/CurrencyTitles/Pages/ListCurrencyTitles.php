<?php

namespace App\Filament\Resources\CurrencyTitles\Pages;

use App\Filament\Resources\CurrencyTitles\CurrencyTitleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCurrencyTitles extends ListRecords
{
    protected static string $resource = CurrencyTitleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
