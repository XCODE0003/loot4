<?php

namespace App\Filament\Resources\StorageUnitIds\Pages;

use App\Filament\Resources\StorageUnitIds\StorageUnitIdResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStorageUnitId extends EditRecord
{
    protected static string $resource = StorageUnitIdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
