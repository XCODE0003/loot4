<?php

namespace App\Filament\Resources\ConversionLogs\Pages;

use App\Enums\ConversionStatus;
use App\Filament\Resources\ConversionLogs\ConversionLogResource;
use App\Models\ConversionLog;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListConversionLogs extends ListRecords
{
    protected static string $resource = ConversionLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clearSkipped')
                ->label('Clear skipped logs')
                ->icon('heroicon-m-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalDescription('Permanently delete every "Skipped" debug log (no-consent / GTM-handled pixel attempts). Sent and failed conversions are kept.')
                ->action(function (): void {
                    $deleted = ConversionLog::query()
                        ->where('status', ConversionStatus::Skipped)
                        ->delete();

                    Notification::make()
                        ->title("Deleted {$deleted} skipped log".($deleted === 1 ? '' : 's'))
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
