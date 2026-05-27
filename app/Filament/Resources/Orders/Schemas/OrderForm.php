<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer & Status')
                    ->columns(2)
                    ->schema([
                        TextInput::make('order_number')
                            ->required()
                            ->maxLength(255)
                            ->disabledOn('edit'),
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->label('User')
                            ->placeholder('Guest'),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required(),
                        TextInput::make('country')
                            ->maxLength(2),
                        Select::make('status')
                            ->options(OrderStatus::class)
                            ->default(OrderStatus::Pending->value)
                            ->required(),
                        Select::make('payment_status')
                            ->options(PaymentStatus::class)
                            ->default(PaymentStatus::Pending->value)
                            ->required(),
                        Select::make('delivery_status')
                            ->options(DeliveryStatus::class)
                            ->default(DeliveryStatus::Pending->value)
                            ->required(),
                        TextInput::make('ip')
                            ->label('IP address')
                            ->maxLength(45),
                    ]),

                Section::make('Payment & Totals')
                    ->columns(3)
                    ->schema([
                        TextInput::make('currency')
                            ->required()
                            ->default('USD')
                            ->maxLength(3),
                        Select::make('coupon_id')
                            ->relationship('coupon', 'code')
                            ->searchable()
                            ->preload()
                            ->label('Coupon'),
                        TextInput::make('discount_code')
                            ->maxLength(255),
                        TextInput::make('subtotal')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                        TextInput::make('discount')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                        TextInput::make('total')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                    ]),

                Section::make('Attribution / Traffic Source')
                    ->collapsible()
                    ->collapsed()
                    ->columns(3)
                    ->schema([
                        TextInput::make('source'),
                        TextInput::make('medium'),
                        TextInput::make('campaign'),
                        TextInput::make('content'),
                        TextInput::make('term'),
                        TextInput::make('landing_page')->label('Landing page'),
                        TextInput::make('fbclid')->label('Facebook Click ID'),
                        TextInput::make('ttclid')->label('TikTok Click ID'),
                        DateTimePicker::make('first_visit_at')->label('First visit'),
                    ]),

                Section::make('Dynamic Form Data & Notes')
                    ->columns(1)
                    ->schema([
                        KeyValue::make('form_data')
                            ->label('Dynamic form data')
                            ->keyLabel('Field')
                            ->valueLabel('Value')
                            ->reorderable(),
                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
