<?php

namespace App\Filament\Resources\UserNames\Pages;

use App\Filament\Resources\UserNames\UserNameResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUserName extends EditRecord
{
    protected static string $resource = UserNameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
