<?php

namespace App\Filament\Resources\ConversionLogs\Schemas;

use App\Enums\ConversionPlatform;
use App\Enums\ConversionStatus;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ConversionLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Event')
                    ->columns(2)
                    ->schema([
                        Select::make('platform')
                            ->options(ConversionPlatform::class)
                            ->required(),
                        TextInput::make('event')
                            ->required()
                            ->placeholder('Purchase'),
                        Select::make('order_id')
                            ->relationship('order', 'order_number')
                            ->searchable()
                            ->preload()
                            ->label('Order'),
                        Select::make('status')
                            ->options(ConversionStatus::class)
                            ->default(ConversionStatus::Pending->value)
                            ->required(),
                        TextInput::make('value')
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('currency')
                            ->default('USD')
                            ->maxLength(3),
                        TextInput::make('url')
                            ->url()
                            ->columnSpanFull(),
                        Textarea::make('reason')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Payloads')
                    ->columns(2)
                    ->schema([
                        KeyValue::make('request_payload')
                            ->keyLabel('Key')
                            ->valueLabel('Value'),
                        KeyValue::make('response_payload')
                            ->keyLabel('Key')
                            ->valueLabel('Value'),
                    ]),
            ]);
    }
}
