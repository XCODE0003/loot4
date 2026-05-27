<?php

namespace App\Filament\Resources\StorageUnits\Schemas;

use App\Enums\StorageUnitStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StorageUnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Stock')
                    ->columns(2)
                    ->schema([
                        Select::make('product_id')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('type')
                            ->placeholder('account / key / code'),
                        TextInput::make('stock')
                            ->numeric()
                            ->default(1)
                            ->required(),
                        Select::make('status')
                            ->options(StorageUnitStatus::class)
                            ->default(StorageUnitStatus::Available->value)
                            ->required(),
                        Select::make('order_id')
                            ->relationship('order', 'order_number')
                            ->searchable()
                            ->preload()
                            ->label('Assigned order'),
                        DateTimePicker::make('expires_at'),
                    ]),

                Section::make('Delivery payload')
                    ->description('Stored encrypted at rest.')
                    ->columns(1)
                    ->schema([
                        Textarea::make('credentials')
                            ->rows(3)
                            ->helperText('Login:password, key, or account credentials.'),
                        Textarea::make('delivery_data')
                            ->rows(3)
                            ->helperText('Instructions or extra data sent to the customer.'),
                        DateTimePicker::make('reserved_at'),
                        DateTimePicker::make('delivered_at'),
                    ]),
            ]);
    }
}
