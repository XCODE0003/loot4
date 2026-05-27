<?php

namespace App\Filament\Resources\TicketSubjects\Pages;

use App\Filament\Resources\TicketSubjects\TicketSubjectResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTicketSubjects extends ListRecords
{
    protected static string $resource = TicketSubjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
