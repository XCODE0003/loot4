<?php

namespace App\Filament\Resources\ConversionLogs\Pages;

use App\Filament\Resources\ConversionLogs\ConversionLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListConversionLogs extends ListRecords
{
    protected static string $resource = ConversionLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
