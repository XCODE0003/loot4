<?php

namespace App\Filament\Resources\TicketSubjects\Pages;

use App\Filament\Resources\TicketSubjects\TicketSubjectResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTicketSubject extends EditRecord
{
    protected static string $resource = TicketSubjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
