<?php

namespace App\Filament\Resources\UserNames\Pages;

use App\Filament\Resources\UserNames\UserNameResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUserName extends CreateRecord
{
    protected static string $resource = UserNameResource::class;
}
