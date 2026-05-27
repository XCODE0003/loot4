<?php

namespace App\Filament\Resources\StorageUnits\Pages;

use App\Filament\Resources\StorageUnits\StorageUnitResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStorageUnit extends EditRecord
{
    protected static string $resource = StorageUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
