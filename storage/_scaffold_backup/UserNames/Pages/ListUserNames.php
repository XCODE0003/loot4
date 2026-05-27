<?php

namespace App\Filament\Resources\UserNames\Pages;

use App\Filament\Resources\UserNames\UserNameResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUserNames extends ListRecords
{
    protected static string $resource = UserNameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
