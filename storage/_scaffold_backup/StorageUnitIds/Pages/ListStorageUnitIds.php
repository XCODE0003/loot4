<?php

namespace App\Filament\Resources\StorageUnitIds\Pages;

use App\Filament\Resources\StorageUnitIds\StorageUnitIdResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStorageUnitIds extends ListRecords
{
    protected static string $resource = StorageUnitIdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
