<?php

namespace App\Filament\Resources\ConversionLogs\Pages;

use App\Filament\Resources\ConversionLogs\ConversionLogResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewConversionLog extends ViewRecord
{
    protected static string $resource = ConversionLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
