<?php

namespace App\Filament\Resources\ConversionLogs\Pages;

use App\Filament\Resources\ConversionLogs\ConversionLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateConversionLog extends CreateRecord
{
    protected static string $resource = ConversionLogResource::class;
}
