<?php

namespace App\Filament\Resources\ConversionLogs\Pages;

use App\Filament\Resources\ConversionLogs\ConversionLogResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditConversionLog extends EditRecord
{
    protected static string $resource = ConversionLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
