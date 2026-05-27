<?php

namespace App\Filament\Resources\StorageUnits\Pages;

use App\Enums\StorageUnitStatus;
use App\Filament\Resources\StorageUnits\StorageUnitResource;
use App\Models\Product;
use App\Models\StorageUnit;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListStorageUnits extends ListRecords
{
    protected static string $resource = StorageUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Import TXT/CSV')
                ->icon('heroicon-m-arrow-up-tray')
                ->schema([
                    Select::make('product_id')
                        ->label('Product')
                        ->options(fn (): array => Product::query()->orderBy('name')->pluck('name', 'id')->all())
                        ->searchable()
                        ->required(),
                    TextInput::make('type')
                        ->placeholder('account / key / code'),
                    Textarea::make('lines')
                        ->label('One credential per line')
                        ->rows(10)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $lines = collect(preg_split('/\r\n|\r|\n/', (string) $data['lines']))
                        ->map(fn (string $line): string => trim($line))
                        ->filter()
                        ->values();

                    foreach ($lines as $line) {
                        StorageUnit::create([
                            'product_id' => $data['product_id'],
                            'type' => $data['type'] ?? null,
                            'stock' => 1,
                            'credentials' => $line,
                            'status' => StorageUnitStatus::Available,
                        ]);
                    }

                    Notification::make()
                        ->title("Imported {$lines->count()} stock units")
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
