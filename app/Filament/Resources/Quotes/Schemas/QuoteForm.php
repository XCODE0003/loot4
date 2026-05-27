<?php

namespace App\Filament\Resources\Quotes\Schemas;

use App\Enums\QuoteStatus;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Request')
                    ->columns(2)
                    ->schema([
                        TextInput::make('email')
                            ->email(),
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Customer'),
                        Select::make('product_id')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Product'),
                        KeyValue::make('fields')
                            ->keyLabel('Field')
                            ->valueLabel('Value')
                            ->columnSpanFull(),
                        Textarea::make('message')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Manager response')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->options(QuoteStatus::class)
                            ->default(QuoteStatus::New->value)
                            ->required(),
                        Select::make('assigned_to')
                            ->relationship('assignedTo', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Assigned to'),
                        TextInput::make('quoted_price')
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('currency')
                            ->default('USD')
                            ->maxLength(3),
                        Textarea::make('manager_response')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
