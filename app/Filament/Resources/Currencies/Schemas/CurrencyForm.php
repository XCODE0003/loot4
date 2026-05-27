<?php

namespace App\Filament\Resources\Currencies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CurrencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Currency')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('code')
                            ->required()
                            ->maxLength(3)
                            ->unique(ignoreRecord: true)
                            ->helperText('ISO code, e.g. USD'),
                        TextInput::make('symbol')
                            ->maxLength(8),
                        TextInput::make('exchange_rate')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->step('0.000001')
                            ->helperText('Rate relative to the default currency.'),
                        Toggle::make('auto_update')
                            ->helperText('Refresh the rate automatically from an external API.'),
                        Toggle::make('is_default')
                            ->label('Default currency'),
                    ]),
            ]);
    }
}
